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
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'balance_amount')) {
                $table->decimal('balance_amount', 15, 2)->after('total_amount')->default(0);
            }
            if (!Schema::hasColumn('purchases', 'status')) {
                $table->string('status')->after('balance_amount')->default('Pending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['balance_amount', 'status']);
        });
    }
};
