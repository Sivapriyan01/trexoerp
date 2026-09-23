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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'anniversary_date')) {
                $table->date('anniversary_date')->nullable();
            }
            if (!Schema::hasColumn('customers', 'anniversary_reminder_enabled')) {
                $table->boolean('anniversary_reminder_enabled')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'anniversary_date')) {
                $table->dropColumn('anniversary_date');
            }
            if (Schema::hasColumn('customers', 'anniversary_reminder_enabled')) {
                $table->dropColumn('anniversary_reminder_enabled');
            }
        });
    }
};
