<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CrmWorkflow;
use App\Models\CrmWorkflowField;
use App\Models\CrmWorkspace;
use App\Models\CrmDashboard;
use App\Models\CrmWidget;
use App\Models\CrmApi;
use App\Models\CrmWorkflowRule;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkflowSettingsController extends Controller
{
    public function permissions()
    {
        $users = TenantUser::with('branch')->get();
        $workspaces = CrmWorkspace::orderBy('id')->get();
        $workflows = CrmWorkflow::orderBy('id')->get();
        
        return view('tenant.crm.permissions', compact('users', 'workspaces', 'workflows'));
    }

    public function updatePermissions(Request $request, TenantUser $user)
    {
        $validated = $request->validate([
            'workspaces' => 'nullable|array',
            'dashboards' => 'nullable|array',
            'settings_access' => 'boolean',
            'workflow_actions' => 'nullable|array', // Structure: {wf_id: [action1, action2]}
        ]);

        $permissions = $user->permissions ?? [];
        
        // Filter out existing CRM specific permissions
        $permissions = array_filter($permissions, function($p) {
            return !str_starts_with($p, 'CRM_');
        });

        // Add Workspace permissions
        foreach ($request->workspaces ?? [] as $wsId) {
            $permissions[] = "CRM_WS:{$wsId}";
        }

        // Add Dashboard permissions
        foreach ($request->dashboards ?? [] as $dbName) {
            $permissions[] = "CRM_DB:{$dbName}";
        }

        // Add Settings access
        if ($request->settings_access) {
            $permissions[] = "CRM_Settings";
        }

        // Add Workflow Action permissions
        foreach ($request->workflow_actions ?? [] as $wfId => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "CRM_WF:{$wfId}:{$action}";
            }
        }

        $user->update(['permissions' => array_values($permissions)]);

        return response()->json(['success' => true, 'message' => 'User permissions updated successfully!']);
    }

    public function index()
    {
        return view('tenant.crm.settings_index');
    }

    public function workflow()
    {
        $workspaces = CrmWorkspace::with(['workflows' => function($q) {
            $q->with('fields')->orderBy('order_index');
        }])->get();
        
        $workflows = CrmWorkflow::with('fields')->orderBy('order_index')->get();
        
        return view('tenant.crm.settings', compact('workspaces', 'workflows'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $workflow = CrmWorkflow::create([
            'name' => $validated['name'],
            'icon' => $request->icon ?? 'lightning-bolt',
            'color' => $request->color ?? 'indigo',
            'order_index' => CrmWorkflow::count(),
        ]);

        return response()->json(['success' => true, 'workflow' => $workflow]);
    }

    public function storeField(Request $request, CrmWorkflow $workflow)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string',
            'is_required' => 'boolean',
        ]);

        $field = $workflow->fields()->create([
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_required' => $request->is_required ?? false,
            'order_index' => $workflow->fields()->count(),
        ]);

        return response()->json(['success' => true, 'field' => $field]);
    }

    public function sync(Request $request)
    {
        $data = $request->input('workspaces', []);

        \DB::transaction(function() use ($data) {
            $workspaceIds = [];
            foreach ($data as $wsIndex => $wsData) {
                $workspace = CrmWorkspace::updateOrCreate(
                    ['id' => $wsData['id'] ?? null],
                    ['name' => $wsData['name']]
                );
                $workspaceIds[] = $workspace->id;

                $workflowIds = [];
                foreach ($wsData['workflows'] ?? [] as $wfIndex => $wfData) {
                    $workflow = $workspace->workflows()->updateOrCreate(
                        ['id' => $wfData['id'] ?? null],
                        [
                            'name' => $wfData['name'],
                            'icon' => $wfData['icon'] ?? 'lightning-bolt',
                            'color' => $wfData['color'] ?? 'indigo',
                            'order_index' => $wfIndex
                        ]
                    );
                    $workflowIds[] = $workflow->id;

                    $fieldIds = [];
                    foreach ($wfData['fields'] ?? [] as $fIndex => $fData) {
                        $field = $workflow->fields()->updateOrCreate(
                            ['id' => $fData['id'] ?? null],
                            [
                                'label' => $fData['label'],
                                'name' => \Illuminate\Support\Str::slug($fData['label'], '_'),
                                'type' => $fData['type'] ?? 'text',
                                'is_required' => $fData['is_required'] ?? false,
                                'order_index' => $fIndex
                            ]
                        );
                        $fieldIds[] = $field->id;
                    }
                    // Clean up removed fields
                    $workflow->fields()->whereNotIn('id', $fieldIds)->delete();
                }
                // Clean up removed workflows
                $workspace->workflows()->whereNotIn('id', $workflowIds)->delete();
            }
            // Clean up removed workspaces
            CrmWorkspace::whereNotIn('id', $workspaceIds)->delete();
        });

        return response()->json(['success' => true, 'message' => 'CRM Architecture Deployed Successfully!']);
    }

    public function dashboards()
    {
        $dashboards = CrmDashboard::with('widgets')->get();
        $workspaces = CrmWorkspace::all();
        $workflows = CrmWorkflow::all();
        return view('tenant.crm.dashboard_settings', compact('dashboards', 'workspaces', 'workflows'));
    }

    public function storeDashboard(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workspace_id' => 'nullable|exists:crm_workspaces,id',
        ]);

        $dashboard = CrmDashboard::create([
            'name' => $validated['name'],
            'workspace_id' => $validated['workspace_id'],
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'dashboard' => $dashboard]);
    }

    public function storeWidget(Request $request, CrmDashboard $dashboard)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'workflow_id' => 'nullable|exists:crm_workflows,id',
            'field_id' => 'nullable|exists:crm_workflow_fields,id',
            'settings' => 'nullable|array',
        ]);

        $widget = $dashboard->widgets()->create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'workflow_id' => $validated['workflow_id'],
            'field_id' => $validated['field_id'],
            'settings' => $validated['settings'] ?? [],
            'order_index' => $dashboard->widgets()->count(),
        ]);

        return response()->json(['success' => true, 'widget' => $widget]);
    }

    public function apis()
    {
        $apis = CrmApi::with(['workspace', 'workflow.fields'])->get();
        $workspaces = CrmWorkspace::with('workflows')->get();
        return view('tenant.crm.api_settings', compact('apis', 'workspaces'));
    }

    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workspace_id' => 'required|exists:crm_workspaces,id',
            'workflow_id' => 'required|exists:crm_workflows,id',
        ]);

        $api = CrmApi::create([
            'name' => $validated['name'],
            'workspace_id' => $validated['workspace_id'],
            'workflow_id' => $validated['workflow_id'],
            'api_key' => 'crm_' . Str::random(20),
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'api' => $api]);
    }

    public function deleteApi(CrmApi $api)
    {
        $api->delete();
        return response()->json(['success' => true]);
    }

    public function deleteDashboard(CrmDashboard $dashboard)
    {
        $dashboard->delete();
        return response()->json(['success' => true]);
    }

    public function deleteWidget(CrmWidget $widget)
    {
        $widget->delete();
        return response()->json(['success' => true]);
    }

    public function storePublicForm(Request $request)
    {
        $validated = $request->validate([
            'workspace_id' => 'required|exists:crm_workspaces,id',
            'workflow_id' => 'required|exists:crm_workflows,id',
            'name' => 'required|string',
            'type' => 'required|in:one-time,permanent',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'settings' => 'required|array',
            'expiry' => 'nullable|date',
        ]);

        $slug = \Illuminate\Support\Str::random(10);

        $form = \App\Models\CrmPublicForm::create([
            'workspace_id' => $validated['workspace_id'],
            'workflow_id' => $validated['workflow_id'],
            'slug' => $slug,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'fields' => $validated['fields'],
            'settings' => $validated['settings'],
            'expiry_at' => $validated['expiry'],
        ]);

        $url = url("/public/leads/{$slug}");

        return response()->json([
            'success' => true,
            'url' => $url,
            'slug' => $slug
        ]);
    }

    public function getPublicForms()
    {
        $forms = \App\Models\CrmPublicForm::with('workflow')->latest()->get();
        return response()->json($forms);
    }

    public function deletePublicForm($id)
    {
        $form = \App\Models\CrmPublicForm::findOrFail($id);
        $form->delete();
        return response()->json(['success' => true]);
    }
}
