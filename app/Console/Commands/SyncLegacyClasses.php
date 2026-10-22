<?php

namespace App\Console\Commands;

use App\Services\LegacyClassSync;
use Illuminate\Console\Command;

class SyncLegacyClasses extends Command
{
    protected $signature = 'library:sync-classes';

    protected $description = 'Copy the old KPSC free / paid class links into the class library';

    public function handle(): int
    {
        $s = (new LegacyClassSync())->syncAll();
        $this->info("Created {$s['created']}, updated {$s['updated']}, skipped {$s['skipped']} (no title or link).");

        return self::SUCCESS;
    }
}
