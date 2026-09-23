<?php
// database/migrations/tenant/2026_04_30_060000_create_crm_public_forms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_public_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('crm_workspaces')->onDelete('cascade');
            $table->foreignId('workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type')->default('permanent'); // one-time, permanent
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('fields')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expiry_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_public_forms');
    }
};
