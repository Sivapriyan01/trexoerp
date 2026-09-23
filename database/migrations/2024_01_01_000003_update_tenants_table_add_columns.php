<?php
// database/migrations/2024_01_01_000003_update_tenants_table_add_columns.php
// Add missing fields to existing tenants table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (! Schema::hasColumn('tenants', 'name')) {
                    $table->string('name')->after('id');
                }

                if (! Schema::hasColumn('tenants', 'email')) {
                    $table->string('email')->unique()->after('name');
                }

                if (! Schema::hasColumn('tenants', 'plan')) {
                    $table->string('plan')->default('basic')->after('email');
                }

                if (! Schema::hasColumn('tenants', 'status')) {
                    $table->string('status')->default('active')->after('plan');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('tenants', 'plan')) {
                    $table->dropColumn('plan');
                }
                if (Schema::hasColumn('tenants', 'email')) {
                    $table->dropUnique(['email']);
                    $table->dropColumn('email');
                }
                if (Schema::hasColumn('tenants', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
};
