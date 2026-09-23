<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CrmWorkflow;
use App\Models\CrmLead;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('tenant')->user();
        $workspacesQuery = \App\Models\CrmWorkspace::with('workflows');

        if (!$user->full_access) {
            $allowedWorkspaceIds = array_filter($user->permissions ?? [], function($p) {
                return str_starts_with($p, 'Workspace:') || str_starts_with($p, 'CRM_WS:');
            });
            $allowedWorkspaceIds = array_map(function($p) {
                return (int) str_replace(['Workspace:', 'CRM_WS:'], '', $p);
            }, $allowedWorkspaceIds);

            if (count($allowedWorkspaceIds) > 0) {
                $workspacesQuery->whereIn('id', $allowedWorkspaceIds);
            }
        }

        $workspaces = $workspacesQuery->get();
        $workflows = CrmWorkflow::with(['fields'])
            ->whereIn('workspace_id', $workspaces->pluck('id'))
            ->withCount('leads')
            ->orderBy('order_index')
            ->get();
        
        $activeWorkflowId = $request->query('workflow');
        $activeWorkspaceId = $request->query('workspace');
        $search = $request->query('search');

        if ($activeWorkflowId === 'null' || $activeWorkflowId === '') $activeWorkflowId = null;
        if ($activeWorkspaceId === 'null' || $activeWorkspaceId === '') $activeWorkspaceId = null;

        $leads = CrmLead::with(['workflow', 'data.field', 'assignee'])
            ->when($activeWorkflowId, function($q) use ($activeWorkflowId) {
                return $q->where('workflow_id', $activeWorkflowId);
            })
            ->when($activeWorkspaceId, function($q) use ($activeWorkspaceId) {
                return $q->whereHas('workflow', function($sq) use ($activeWorkspaceId) {
                    $sq->where('workspace_id', $activeWorkspaceId);
                });
            })
            ->when($search, function($q) use ($search) {
                return $q->whereHas('data', function($sq) use ($search) {
                    $sq->where('value', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return view('tenant.crm.index', compact('workspaces', 'workflows', 'leads', 'activeWorkflowId', 'activeWorkspaceId'));
    }

    public function dashboard()
    {
        $user = auth('tenant')->user();
        $workspacesQuery = \App\Models\CrmWorkspace::with('workflows');

        if (!$user->full_access) {
            $allowedWorkspaceIds = array_filter($user->permissions ?? [], function($p) {
                return str_starts_with($p, 'Workspace:') || str_starts_with($p, 'CRM_WS:');
            });
            $allowedWorkspaceIds = array_map(function($p) {
                return (int) str_replace(['Workspace:', 'CRM_WS:'], '', $p);
            }, $allowedWorkspaceIds);

            if (count($allowedWorkspaceIds) > 0) {
                $workspacesQuery->whereIn('id', $allowedWorkspaceIds);
            }
        }

        $workspaces = $workspacesQuery->get();
        $workflows = CrmWorkflow::whereIn('workspace_id', $workspaces->pluck('id'))
            ->withCount('leads')
            ->get();

        $dashboards = \App\Models\CrmDashboard::with('widgets')->get();

        if ($dashboards->isEmpty()) {
            $defaultDashboard = \App\Models\CrmDashboard::create([
                'name' => 'Main Dashboard',
                'created_by' => $user->id ?? null,
            ]);
            
            $defaultDashboard->widgets()->create([
                'title' => 'Total Leads',
                'type' => 'number',
                'order_index' => 0
            ]);
            
            $dashboards = \App\Models\CrmDashboard::with('widgets')->get();
        }

        return view('tenant.crm.dashboard', compact('workspaces', 'workflows', 'dashboards'));
    }

    public function create()
    {
        $workflows = CrmWorkflow::with('fields')->get();
        return view('tenant.crm.create', compact('workflows'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'workflow_id' => 'required|exists:crm_workflows,id',
            'fields' => 'required|array'
        ]);

        $lead = CrmLead::create([
            'workflow_id' => $request->workflow_id,
            'assigned_to' => auth()->id() ?? 1, // Default to super admin for demo
            'status' => 'Active'
        ]);

        $workflow = CrmWorkflow::with('fields')->find($request->workflow_id);

        foreach ($request->fields as $fieldName => $value) {
            $field = $workflow->fields()->where('name', $fieldName)->first();
            if ($field) {
                \App\Models\CrmLeadData::create([
                    'lead_id' => $lead->id,
                    'field_id' => $field->id,
                    'value' => $value
                ]);
            }
        }

        return redirect()->route('tenant.crm.index')->with('success', 'Lead created successfully!');
    }

    public function export(Request $request)
    {
        $leads = CrmLead::with(['workflow', 'data.field'])->get();
        $filename = "crm_leads_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            
            // Headers
            fputcsv($handle, ['ID', 'Workflow', 'Bill Name', 'Email', 'Mobile', 'Status', 'Created At']);

            foreach ($leads as $lead) {
                /** @var \App\Models\CrmLead $lead */
                fputcsv($handle, [
                    $lead->id,
                    $lead->workflow->name ?? 'N/A',
                    $lead->getFieldValue('Bill Name'),
                    $lead->getFieldValue('Email'),
                    $lead->getFieldValue('Mobile'),
                    $lead->status,
                    $lead->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
    public function edit(CrmLead $lead)
    {
        $lead->load(['workflow', 'data.field', 'assignee']);
        return view('tenant.crm.edit', compact('lead'));
    }

    public function update(Request $request, CrmLead $lead)
    {
        $lead->update([
            'status' => $request->input('status', $lead->status),
        ]);

        if ($request->has('data')) {
            foreach ($request->input('data') as $fieldId => $value) {
                \App\Models\CrmLeadData::updateOrCreate(
                    ['lead_id' => $lead->id, 'field_id' => $fieldId],
                    ['value' => $value]
                );
            }
        }

        return redirect()->route('tenant.crm.index')->with('success', 'Lead updated successfully!');
    }

    public function destroy(CrmLead $lead)
    {
        $lead->delete();
        if(request()->expectsJson()) {
            return response()->json(['message' => 'Lead deleted']);
        }
        return redirect()->route('tenant.crm.index')->with('success', 'Lead deleted successfully!');
    }
}
