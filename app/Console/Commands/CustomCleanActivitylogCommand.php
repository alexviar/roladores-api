<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Spatie\Activitylog\ActivitylogServiceProvider;

class CustomCleanActivitylogCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'activitylog:clean-before-today';

    protected $description = 'Clean up old records from the activity log.';

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->comment('Cleaning activity log...');

        $cutOffDate = Carbon::today()->format('Y-m-d');

        $activity = ActivitylogServiceProvider::getActivityModelInstance();

        $amountDeleted = $activity::query()
            ->where('created_at', '<', $cutOffDate)
            ->delete();

        $this->info("Deleted {$amountDeleted} record(s) from the activity log.");

        $this->comment('All done!');
    }
}
