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
        Schema::create('rma_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rma_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('condition')->nullable(); // new, defective, damaged
            $table->text('reason')->nullable();
            $table->string('inspection_status')->nullable(); // pending, approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rma_request_items');
    }
};
