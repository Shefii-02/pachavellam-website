<?php

namespace App\Console\Commands;

use App\Services\TrashPurger;
use Illuminate\Console\Command;

class PurgeTrash extends Command
{
    protected $signature = 'trash:purge {--days=60 : remove what has been in Trash longer than this}';

    protected $description = 'Permanently remove old items from Trash (with their pictures and files)';

    public function handle(): int
    {
        $n = TrashPurger::purgeOlderThan((int) $this->option('days'));
        $this->info("Removed {$n} item(s) from Trash.");

        return self::SUCCESS;
    }
}
