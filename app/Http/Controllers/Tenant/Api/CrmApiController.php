<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CrmApiController extends Controller
{
    // GET /api/v1/crm/leads
    public function index(Request $request)
    {
        $query = CrmLead::with(['assignee', 'data.field', 'workflow'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $leads = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $leads->items(),
            'meta'    => ['current_page' => $leads->currentPage(), 'last_page' => $leads->lastPage(), 'total' => $leads->total()],
        ]);
    }

    // POST /api/v1/crm/leads
    public function store(Request $request)
    {
        $request->validate([
            'workflow_id' => 'required|exists:crm_workflows,id',
            'fields'      => 'required|array',
            'source'      => 'nullable|string',
        ]);

        $lead = CrmLead::create([
            'workflow_id' => $request->workflow_id,
            'assigned_to' => auth()->id() ?? 1,
            'status'      => 'new',
            'source'      => $request->source,
        ]);

        $workflow = \App\Models\CrmWorkflow::with('fields')->find($request->workflow_id);

        foreach ($request->fields as $fieldName => $value) {
            $field = $workflow->fields()->where('name', $fieldName)->orWhere('label', $fieldName)->first();
            if ($field) {
                \App\Models\CrmLeadData::create([
                    'lead_id'  => $lead->id,
                    'field_id' => $field->id,
                    'value'    => $value
                ]);
            }
        }

        return response()->json(['success' => true, 'data' => $lead->load('data.field')], 201);
    }

    // GET /api/v1/crm/leads/{id}
    public function show($id)
    {
        $lead = CrmLead::with(['assignee', 'data.field', 'workflow'])->find($id);

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $lead]);
    }

    // PUT /api/v1/crm/leads/{id}
    public function update(Request $request, $id)
    {
        $lead = CrmLead::find($id);

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        if ($request->has('status')) {
            $lead->update(['status' => $request->status]);
        }

        if ($request->has('source')) {
            $lead->update(['source' => $request->source]);
        }

        if ($request->has('fields')) {
            $workflow = \App\Models\CrmWorkflow::with('fields')->find($lead->workflow_id);
            foreach ($request->fields as $fieldName => $value) {
                $field = $workflow->fields()->where('name', $fieldName)->orWhere('label', $fieldName)->first();
                if ($field) {
                    \App\Models\CrmLeadData::updateOrCreate(
                        ['lead_id' => $lead->id, 'field_id' => $field->id],
                        ['value' => $value]
                    );
                }
            }
        }

        return response()->json(['success' => true, 'data' => $lead->load('data.field')]);
    }

    // DELETE /api/v1/crm/leads/{id}
    public function destroy($id)
    {
        $lead = CrmLead::find($id);

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $lead->delete();

        return response()->json(['success' => true, 'message' => 'Lead deleted.']);
    }

    // GET /api/v1/crm/dashboard
    public function dashboard()
    {
        $total    = CrmLead::count();
        $byStatus = CrmLead::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $bySource = CrmLead::selectRaw('source, count(*) as count')->whereNotNull('source')->groupBy('source')->pluck('count', 'source');

        return response()->json([
            'success' => true,
            'data'    => [
                'total'     => $total,
                'by_status' => $byStatus,
                'by_source' => $bySource,
            ],
        ]);
    }

    // GET /api/v1/crm/leads/statuses
    public function statuses()
    {
        $statuses = CrmLead::select('status')->distinct()->whereNotNull('status')->pluck('status');

        return response()->json(['success' => true, 'data' => $statuses]);
    }
}
