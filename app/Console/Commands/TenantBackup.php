<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class TenantBackup extends Command
{
    protected $signature = 'tenant:backup {tenant?}';
    protected $description = 'Backup tenant databases using pg_dump';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $tenants = $tenantId ? Tenant::where('id', $tenantId)->get() : Tenant::all();

        $directory = storage_path('app/backups/tenants');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $host = config('database.connections.pgsql.host', '127.0.0.1');
        $port = config('database.connections.pgsql.port', '5432');
        $username = config('database.connections.pgsql.username', 'postgres');
        $password = config('database.connections.pgsql.password', '1234567890');

        // Set PGPASSWORD environment variable so pg_dump doesn't prompt
        putenv("PGPASSWORD={$password}");

        $telegramToken = env('TELEGRAM_BOT_TOKEN');
        $telegramChatId = env('TELEGRAM_CHAT_ID');

        foreach ($tenants as $tenant) {
            $dbName = config('tenancy.database.prefix') . $tenant->id;
            $filename = "{$tenant->id}-" . now()->format('Y-m-d-H-i-s') . ".sql";
            $filepath = $directory . '/' . $filename;

            $this->info("Backing up {$dbName} to {$filename}...");

            $command = sprintf(
                'pg_dump -h %s -p %s -U %s -d %s -F p -f %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            $returnVar = null;
            $output = null;
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                $this->info("Successfully backed up {$tenant->id}");
                
                // Small delay to ensure pg_dump has completely released the file lock
                sleep(1);
                
                if (!file_exists($filepath) || filesize($filepath) === 0) {
                    $this->error("Backup file is missing or empty: {$filepath}");
                    continue;
                }

                // Zip the file
                $zipFilename = str_replace('.sql', '.zip', $filename);
                $zipFilepath = $directory . DIRECTORY_SEPARATOR . $zipFilename;
                
                $zip = new \ZipArchive();
                if ($zip->open($zipFilepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                    $zip->addFile($filepath, $filename);
                    if ($zip->close()) {
                        // We can delete the original sql file now that it's zipped
                        @unlink($filepath);
                    } else {
                        $this->error("Failed to close/save zip file for {$tenant->id}");
                        continue; // Skip telegram send if zip failed
                    }
                } else {
                    $this->error("Failed to create zip file for {$tenant->id}");
                    continue; // Skip telegram send if zip failed
                }
                
                // Send to Telegram if credentials are set
                if ($telegramToken && $telegramChatId) {
                    $this->info("Sending {$zipFilename} to Telegram...");
                    
                    try {
                        $response = Http::timeout(10)->withoutVerifying()->attach(
                            'document', file_get_contents($zipFilepath), $zipFilename
                        )->post("https://api.telegram.org/bot{$telegramToken}/sendDocument", [
                            'chat_id' => $telegramChatId,
                            'caption' => "✅ Backup for Tenant: `{$tenant->id}`\nDate: " . now()->format('Y-m-d H:i:s'),
                            'parse_mode' => 'Markdown'
                        ]);

                        if ($response->successful()) {
                            $this->info("Successfully sent to Telegram.");
                        } else {
                            $this->warn("Failed to send to Telegram: " . $response->body());
                        }
                    } catch (\Throwable $e) {
                        $this->warn("Telegram notification skipped or failed: " . $e->getMessage());
                    }
                }
            } else {
                $this->error("Failed to backup {$tenant->id}");
            }
        }
    }
}
