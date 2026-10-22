<?php

namespace App\Console\Commands;

use App\Services\LegacyQuestionSync;
use Illuminate\Console\Command;

class SyncDailyExamQuestions extends Command
{
    protected $signature = 'library:sync-daily-exams';

    protected $description = 'Copy the questions of the daily, CA daily and model exams into the KPSC question library';

    public function handle(): int
    {
        $stats = (new LegacyQuestionSync())->syncAll();
        $this->info("Created {$stats['created']}, updated {$stats['updated']}, without a matching answer {$stats['no_answer']}, empty {$stats['empty']}.");

        return self::SUCCESS;
    }
}
