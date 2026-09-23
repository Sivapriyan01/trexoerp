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
        Schema::create('service_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_id')->unique();
            $table->date('claim_date');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('product_name');
            $table->text('issue_description');
            $table->string('status')->default('In Progress');
            $table->string('resolution_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_claims');
    }
};
