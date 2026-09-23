<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('cost_electricity', 12, 2)->default(0)->after('estimated_value');
            $table->decimal('cost_water_bill', 12, 2)->default(0)->after('cost_electricity');
            $table->decimal('cost_raw_material', 12, 2)->default(0)->after('cost_water_bill');
            $table->decimal('cost_labour', 12, 2)->default(0)->after('cost_raw_material');
            $table->text('cost_notes')->nullable()->after('cost_labour');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn(['cost_electricity', 'cost_water_bill', 'cost_raw_material', 'cost_labour', 'cost_notes']);
        });
    }
};
