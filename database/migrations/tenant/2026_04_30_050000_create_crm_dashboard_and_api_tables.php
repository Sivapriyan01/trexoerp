<?php
// database/migrations/tenant/2026_04_30_050000_create_crm_dashboard_and_api_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Dashboards ────────────────────────────────────────────────
        Schema::create('crm_dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('workspace_id')->nullable()->constrained('crm_workspaces')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // ── Widgets ───────────────────────────────────────────────────
        Schema::create('crm_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('crm_dashboards')->onDelete('cascade');
            $table->string('title');
            $table->string('type')->default('number'); // number, bar_chart, pie_chart
            $table->foreignId('workflow_id')->nullable()->constrained('crm_workflows')->onDelete('set null');
            $table->foreignId('field_id')->nullable()->constrained('crm_workflow_fields')->onDelete('set null'); // For grouping
            $table->json('settings')->nullable(); // For colors, chart options, filters
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // ── APIs ──────────────────────────────────────────────────────
        Schema::create('crm_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('workspace_id')->constrained('crm_workspaces')->onDelete('cascade');
            $table->foreignId('workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->string('api_key')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_apis');
        Schema::dropIfExists('crm_widgets');
        Schema::dropIfExists('crm_dashboards');
    }
};
