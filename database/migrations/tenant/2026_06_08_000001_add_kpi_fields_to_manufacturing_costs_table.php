<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->string('shift')->nullable()->default('B SHIFT ONLY');
            $table->integer('bags_40kg')->default(0);
            $table->integer('target_bags')->default(414);
            $table->string('downtime')->nullable()->default('Not mentioned');
            $table->integer('labour_count')->default(0);
            $table->decimal('welfare', 12, 2)->default(0);
            $table->decimal('power_units', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->dropColumn([
                'shift',
                'bags_40kg',
                'target_bags',
                'downtime',
                'labour_count',
                'welfare',
                'power_units'
            ]);
        });
    }
};
