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
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable();
                $table->string('invoice_ref');
                $table->date('invoice_date');
                $table->text('remark')->nullable();
                $table->decimal('discount_percent', 8, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('gst_percent', 8, 2)->default(0);
                $table->decimal('gst_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase');
    }
};
