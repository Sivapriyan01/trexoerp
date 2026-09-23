<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crm_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('lightning-bolt');
            $table->string('color')->default('indigo');
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_workflow_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->string('label');
            $table->string('type')->default('text'); // text, number, date, select, textarea
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // For select types
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('crm_lead_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->onDelete('cascade');
            $table->foreignId('field_id')->constrained('crm_workflow_fields')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->foreignId('target_workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->json('logic_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_workflow_rules');
        Schema::dropIfExists('crm_lead_data');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_workflow_fields');
        Schema::dropIfExists('crm_workflows');
    }
};
