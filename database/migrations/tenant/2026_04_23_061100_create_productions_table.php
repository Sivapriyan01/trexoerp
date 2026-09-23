<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('total_qty');
            $table->string('current_stage')->default('Cutting'); // Cutting, Coding, Folding, Stitching, Packing
            $table->string('status')->default('In Progress'); // In Progress, Completed, Hold
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->onDelete('cascade');
            $table->string('from_stage');
            $table->string('to_stage');
            $table->foreignId('action_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_logs');
        Schema::dropIfExists('productions');
    }
};
