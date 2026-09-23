<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Drop old FK
            $table->dropForeign(['created_by']);
            
            // Re-add FK pointing to 'users' table
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Drop the redundant empty table
        Schema::dropIfExists('tenant_users');
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
