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
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->date('deadline_date')->nullable();
            $table->string('priority')->default('Normal'); // Urgent, Normal, Low
            $table->decimal('estimated_value', 15, 2)->default(0);
            $table->text('specifications')->nullable();
            $table->json('assigned_processes')->nullable(); // Order of processes
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'deadline_date', 'priority', 'estimated_value', 'specifications', 'assigned_processes']);
        });
    }
};
