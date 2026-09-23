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
        Schema::table('productions', function (Blueprint $table) {
            $table->timestamp('current_stage_started_at')->nullable();
            $table->text('current_stage_notes')->nullable();
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn(['current_stage_started_at', 'current_stage_notes']);
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
