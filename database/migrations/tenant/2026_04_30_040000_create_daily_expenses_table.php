<?php
// database/migrations/tenant/2026_04_30_040000_create_daily_expenses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            $table->string('type')->default('expense'); // expense | petty_cash
            $table->string('category')->nullable();
            $table->string('description')->nullable();
            $table->string('source')->nullable();        // for petty_cash inward (e.g. "Cash", "Bank")
            $table->string('payment_mode')->default('Cash'); // Cash | Bank | UPI
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_expenses');
    }
};
