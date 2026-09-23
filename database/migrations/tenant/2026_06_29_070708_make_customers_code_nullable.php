<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only act if the 'code' column exists on customers
        if (Schema::hasColumn('customers', 'code')) {
            // First set existing NULLs-to-be to empty string to avoid constraint issues
            DB::table('customers')->whereNull('code')->update(['code' => '']);
            
            Schema::table('customers', function (Blueprint $table) {
                $table->string('code')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // No reverse — we don't want to re-add NOT NULL constraint
    }
};
