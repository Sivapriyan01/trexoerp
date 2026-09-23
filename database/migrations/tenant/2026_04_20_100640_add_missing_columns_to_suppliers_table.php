<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'name')) $table->string('name')->nullable();
            if (!Schema::hasColumn('suppliers', 'phone')) $table->string('phone')->nullable();
            if (!Schema::hasColumn('suppliers', 'email')) $table->string('email')->nullable();
            if (!Schema::hasColumn('suppliers', 'address')) $table->text('address')->nullable();
            if (!Schema::hasColumn('suppliers', 'gstin')) $table->string('gstin')->nullable();
            if (!Schema::hasColumn('suppliers', 'is_active')) $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['name', 'phone', 'email', 'address', 'gstin', 'is_active']);
        });
    }
};
