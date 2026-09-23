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
        Schema::create('purchase_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('suppliers')->onDelete('cascade');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_mode')->default('Cash');
            $table->string('document_number')->nullable();
            $table->text('description')->nullable();
            $table->string('entry_type')->default('payment'); // payment, adjustment, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_settlements');
    }
};
