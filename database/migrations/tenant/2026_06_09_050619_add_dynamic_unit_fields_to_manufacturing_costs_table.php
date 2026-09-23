<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->decimal('total_qty', 12, 2)->default(0);
            $table->string('qty_unit')->default('KG');
            $table->decimal('weight_per_pc', 12, 2)->default(40);
        });
    }

    public function down(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->dropColumn(['total_qty', 'qty_unit', 'weight_per_pc']);
        });
    }
};
