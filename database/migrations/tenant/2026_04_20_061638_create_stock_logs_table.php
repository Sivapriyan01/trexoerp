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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment'])->default('in');
            $table->integer('quantity');
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->unsignedBigInteger('reference_id')->nullable(); // Bill ID or Purchase ID
            $table->string('reference_type')->nullable(); // 'bill' or 'purchase'
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
