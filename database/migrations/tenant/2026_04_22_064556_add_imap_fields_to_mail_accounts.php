<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table) {
            $table->string('imap_host')->nullable()->after('smtp_password');
            $table->integer('imap_port')->default(993)->after('imap_host');
            $table->string('imap_encryption')->default('ssl')->after('imap_port');
        });
    }

    public function down(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table) {
            $table->dropColumn(['imap_host', 'imap_port', 'imap_encryption']);
        });
    }
};
