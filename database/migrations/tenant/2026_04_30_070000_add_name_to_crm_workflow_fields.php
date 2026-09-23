<?php
// database/migrations/tenant/2026_04_30_070000_add_name_to_crm_workflow_fields.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_workflow_fields', function (Blueprint $table) {
            $table->string('name')->nullable()->after('label');
        });
        
        // Populate names from labels
        \App\Models\CrmWorkflowField::all()->each(function ($field) {
            $field->update(['name' => \Illuminate\Support\Str::slug($field->label, '_')]);
        });
    }

    public function down(): void
    {
        Schema::table('crm_workflow_fields', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
