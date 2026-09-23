<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();

                $table->string('product_name');
                $table->string('product_type')->nullable();
                $table->string('size')->nullable();
                $table->string('brand')->nullable();

                $table->integer('quantity');
                $table->decimal('buy_price', 10, 2);

                $table->decimal('total_amount', 10, 2)->default(0);
                $table->decimal('discount_percent', 8, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('gst_percent', 8, 2)->default(0);
                $table->decimal('gst_amount', 10, 2)->default(0);
                $table->decimal('grand_total', 10, 2)->default(0);

                $table->decimal('profit_percent', 8, 2)->default(0);
                $table->decimal('profit_amount', 10, 2)->default(0);

                $table->decimal('mrp', 10, 2)->nullable();
                $table->decimal('dealer_price', 10, 2)->nullable();

                $table->string('barcode')->nullable();
                $table->string('color')->nullable();
                $table->string('image')->nullable();

                $table->timestamps();
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_item');
    }
};
