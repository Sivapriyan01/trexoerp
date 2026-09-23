<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_costs', function (Blueprint $table) {
            $table->id();
            $table->date('month'); // Stored as first day of month: 2026-05-01
            $table->decimal('electricity', 12, 2)->default(0);
            $table->decimal('water_bill', 12, 2)->default(0);
            $table->decimal('raw_material', 12, 2)->default(0);
            $table->decimal('labour_charge', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_costs');
    }
};
