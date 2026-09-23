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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('pre_order_available')->default(false);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->date('expected_delivery_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('pre_order_available');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('expected_delivery_date');
        });
    }
};
