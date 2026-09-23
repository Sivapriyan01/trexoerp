<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Main Instalment Record (Linked to a Bill)
        Schema::create('instalments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2);
            
            $table->string('status')->default('pending'); // pending, completed, overdue
            $table->date('next_due_date')->nullable();
            
            $table->timestamps();
        });

        // 2. Individual Payment Schedule / Breakdown
        Schema::create('instalment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instalment_id')->constrained()->onDelete('cascade');
            
            $table->integer('instalment_no');
            $table->string('type')->default('instalment'); // advance, instalment
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            
            $table->string('status')->default('pending'); // pending, paid, overdue
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
        });

        // 3. Payment History
        Schema::create('instalment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instalment_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('instalment_schedules')->onDelete('set null');
            
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash');
            $table->string('remark')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instalment_payments');
        Schema::dropIfExists('instalment_schedules');
        Schema::dropIfExists('instalments');
    }
};
