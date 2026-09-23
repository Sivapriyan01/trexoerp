<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:database-backup')]
#[Description('Command description')]
class DatabaseBackup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
