<?php

namespace Database\Seeders;

use App\Models\CrmWorkflow;
use App\Models\CrmWorkflowField;
use App\Models\CrmLead;
use App\Models\CrmLeadData;
use App\Models\CrmWorkspace;
use Illuminate\Database\Seeder;

class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\CrmWidget::query()->delete();
        \App\Models\CrmDashboard::query()->delete();
        CrmLeadData::query()->delete();
        CrmLead::query()->delete();
        CrmWorkflowField::query()->delete();
        CrmWorkflow::query()->delete();
        CrmWorkspace::query()->delete();

        // 0. Create Workspaces
        $marketing = CrmWorkspace::create(['name' => 'Marketing', 'icon' => 'speakerphone', 'color' => 'emerald']);
        $manufacturing = CrmWorkspace::create(['name' => 'Manufacturing', 'icon' => 'cog', 'color' => 'blue']);

        // 1. Create Workflows
        $leads = CrmWorkflow::create(['name' => 'Leads', 'icon' => 'lightning-bolt', 'color' => 'indigo', 'order_index' => 0, 'workspace_id' => $marketing->id]);
        $confirm = CrmWorkflow::create(['name' => 'Confirm', 'icon' => 'check', 'color' => 'blue', 'order_index' => 1, 'workspace_id' => $marketing->id]);
        $payment = CrmWorkflow::create(['name' => 'Payment Done', 'icon' => 'currency-dollar', 'color' => 'emerald', 'order_index' => 2, 'workspace_id' => $marketing->id]);
        
        $meta = CrmWorkflow::create(['name' => 'Meta-Leads', 'icon' => 'chat', 'color' => 'violet', 'order_index' => 3, 'workspace_id' => $manufacturing->id]);
        $approved = CrmWorkflow::create(['name' => 'Approved Leads', 'icon' => 'shield-check', 'color' => 'amber', 'order_index' => 4, 'workspace_id' => $manufacturing->id]);

        // 2. Define Standard Fields for all workflows (matching demo)
        $workflows = [$leads, $confirm, $payment, $meta, $approved];
        $user = \App\Models\User::first();

        foreach ($workflows as $wf) {
            $f1 = $wf->fields()->create(['label' => 'Bill Name', 'type' => 'text', 'order_index' => 0]);
            $f2 = $wf->fields()->create(['label' => 'Email', 'type' => 'text', 'order_index' => 1]);
            $f3 = $wf->fields()->create(['label' => 'Mobile', 'type' => 'text', 'order_index' => 2]);
            $f4 = $wf->fields()->create(['label' => 'Address', 'type' => 'textarea', 'order_index' => 3]);

            // Create 3 leads for each workflow
            for ($i = 1; $i <= 3; $i++) {
                $lead = CrmLead::create([
                    'workflow_id' => $wf->id,
                    'assigned_to' => $user->id ?? null,
                    'status' => 'Active'
                ]);

                CrmLeadData::create(['lead_id' => $lead->id, 'field_id' => $f1->id, 'value' => $wf->name . " Customer $i"]);
                CrmLeadData::create(['lead_id' => $lead->id, 'field_id' => $f2->id, 'value' => strtolower($wf->name) . "$i@example.com"]);
                CrmLeadData::create(['lead_id' => $lead->id, 'field_id' => $f3->id, 'value' => "98765432" . $wf->id . $i]);
                CrmLeadData::create(['lead_id' => $lead->id, 'field_id' => $f4->id, 'value' => "Business Park, Sector " . ($wf->id + $i)]);
            }
        }

        // 3. Create Default Dashboard
        $dashboard = \App\Models\CrmDashboard::create([
            'name' => 'Main Overview',
            'created_by' => $user->id ?? null,
        ]);
        
        $dashboard->widgets()->create([
            'title' => 'Total Active Leads',
            'type' => 'number',
            'order_index' => 0
        ]);
        $dashboard->widgets()->create([
            'title' => 'Marketing Leads',
            'type' => 'number',
            'workflow_id' => $leads->id,
            'order_index' => 1
        ]);
    }
}
