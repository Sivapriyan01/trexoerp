<?php
// database/migrations/2024_01_01_000002_create_tenants_table.php
// This is auto-published by stancl/tenancy — customize here

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->string('id')->primary();       // e.g. "square-demo"
                $table->string('name');
                $table->string('email')->unique();
                $table->string('plan')->default('basic'); // basic, pro, enterprise
                $table->string('status')->default('active'); // active, suspended, trial
                $table->json('data')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('domains')) {
            Schema::create('domains', function (Blueprint $table) {
                $table->increments('id');
                $table->string('domain')->unique();      // demo.square.in
                $table->string('tenant_id');
                $table->timestamps();

                $table->foreign('tenant_id')
                      ->references('id')
                      ->on('tenants')
                      ->onUpdate('cascade')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
        // Only drop tenants if they were created by this migration and are not used elsewhere.
        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::dropIfExists('tenants');
    }
};
