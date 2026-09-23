<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CrmPublicForm;
use App\Models\CrmLead;
use App\Models\CrmLeadData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmPublicController extends Controller
{
    public function show($slug)
    {
        \Illuminate\Support\Facades\Log::info('Public Lead Form requested', ['slug' => $slug]);
        
        $form = CrmPublicForm::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$form) {
            \Illuminate\Support\Facades\Log::warning('Public Lead Form not found or inactive', ['slug' => $slug]);
            abort(404);
        }

        $form->load(['workflow.fields', 'workflow.workspace']);

        return view('tenant.crm.public_form', compact('form'));
    }

    public function submit(Request $request, $slug)
    {
        $form = CrmPublicForm::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Validate dynamic fields based on form config
        $rules = [];
        foreach ($form->fields as $fieldName => $config) {
            if (!empty($config['enabled'])) {
                $rule = [];
                if (!empty($config['required'])) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }
                $rules[$fieldName] = $rule;
            }
        }

        $validatedData = $request->validate($rules);

        return DB::transaction(function () use ($form, $validatedData) {
            $lead = CrmLead::create([
                'workflow_id' => $form->workflow_id,
                'status' => 'New', // Or default status from workflow
            ]);

            foreach ($validatedData as $key => $value) {
                $field = $form->workflow->fields()->where('name', $key)->first()
                        ?? $form->workflow->fields()->where('label', $key)->first();
                if ($field) {
                    CrmLeadData::create([
                        'lead_id' => $lead->id,
                        'field_id' => $field->id,
                        'value' => $value,
                    ]);
                }
            }

            // If one-time link, deactivate it
            if ($form->type === 'one-time') {
                $form->update(['is_active' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => $form->settings['successMessage'] ?? 'Lead submitted successfully!',
            ]);
        });
    }
}
