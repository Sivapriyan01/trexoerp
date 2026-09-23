<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('cost_machine_maintenance', 12, 2)->default(0)->after('cost_labour');
            $table->decimal('cost_packing', 12, 2)->default(0)->after('cost_machine_maintenance');
            $table->decimal('cost_transport', 12, 2)->default(0)->after('cost_packing');
            $table->decimal('cost_wastage', 12, 2)->default(0)->after('cost_transport');
            $table->decimal('cost_other', 12, 2)->default(0)->after('cost_wastage');
            $table->decimal('cost_welfare', 12, 2)->default(0)->after('cost_other');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn([
                'cost_machine_maintenance',
                'cost_packing',
                'cost_transport',
                'cost_wastage',
                'cost_other',
                'cost_welfare'
            ]);
        });
    }
};
