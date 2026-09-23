<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->decimal('machine_maintenance', 12, 2)->default(0);
            $table->decimal('packing_cost', 12, 2)->default(0);
            $table->decimal('transport_loading', 12, 2)->default(0);
            $table->decimal('wastage_cost', 12, 2)->default(0);
            $table->decimal('other_expenses', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->dropColumn([
                'machine_maintenance',
                'packing_cost',
                'transport_loading',
                'wastage_cost',
                'other_expenses'
            ]);
        });
    }
};
