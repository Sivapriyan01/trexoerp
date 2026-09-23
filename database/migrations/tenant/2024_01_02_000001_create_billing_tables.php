<?php
// database/migrations/tenant/2024_01_02_000001_create_billing_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Products / Categories ──────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable()->unique();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('product_type')->nullable();
            $table->string('model')->nullable();
            $table->string('size')->nullable();
            $table->string('hsn')->nullable();
            $table->decimal('mrp', 12, 2)->default(0);
            $table->decimal('dealer_price', 12, 2)->default(0);
            $table->decimal('gst', 5, 2)->default(18);
            $table->decimal('cgst', 5, 2)->default(9);
            $table->decimal('sgst', 5, 2)->default(9);
            $table->integer('stock')->default(0);
            $table->integer('low_stock_alert')->default(0);
            $table->string('color')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Customers ──────────────────────────────────────────────────
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable()->index();
            $table->text('address')->nullable();
            $table->decimal('points', 10, 2)->default(0);
            $table->integer('bill_count')->default(0);
            $table->timestamps();
        });

        // ── Bills (Invoice Header) ─────────────────────────────────────
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();       // INV-20260417-001
            $table->string('bill_type')->default('billing'); // billing | outward | quick
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_phone')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('bill_date');

            // Amounts
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(18);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('round_off', 8, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            // Payment
            $table->string('payment_mode')->default('cash'); // cash|card|qr|credit
            $table->string('status')->default('completed');  // completed|draft|returned

            // Meta
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // ── Bill Items (Invoice Lines) ─────────────────────────────────
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Snapshot at time of billing
            $table->string('barcode')->nullable();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('product_type')->nullable();
            $table->string('model')->nullable();
            $table->string('size')->nullable();
            $table->string('hsn')->nullable();
            $table->decimal('mrp', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 12, 2)->default(0);
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('categories');
    }
};
