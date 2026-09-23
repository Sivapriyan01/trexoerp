<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class TenantRestore extends Command
{
    protected $signature = 'tenant:restore {tenant} {file}';
    protected $description = 'Import (restore) a SQL file into a tenant database';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $file = $this->argument('file');

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant {$tenantId} not found.");
            return;
        }

        if (!file_exists($file)) {
            $this->error("File {$file} does not exist.");
            return;
        }

        $host = config('database.connections.pgsql.host', '127.0.0.1');
        $port = config('database.connections.pgsql.port', '5432');
        $username = config('database.connections.pgsql.username', 'postgres');
        $password = config('database.connections.pgsql.password', '1234567890');
        
        // PGPASSWORD environment variable
        putenv("PGPASSWORD={$password}");
        
        $dbName = config('tenancy.database.prefix') . $tenant->id;
        $this->info("Restoring {$file} to {$dbName}...");

        $command = sprintf(
            'psql -h %s -p %s -U %s -d %s -f %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($dbName),
            escapeshellarg($file)
        );

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Successfully imported into {$tenant->id}");
        } else {
            $this->error("Failed to import. Check if psql is installed and DB exists.");
        }
    }
}
