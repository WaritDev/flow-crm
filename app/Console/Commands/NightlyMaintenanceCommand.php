<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NightlyMaintenanceCommand extends Command
{
    protected $signature = 'flowcrm:nightly-maintenance';

    protected $description = 'Clear dashboard cache keys every midnight.';

    public function handle(): int
    {
        $months = range(1, 12);
        $years = [now()->year - 1, now()->year, now()->year + 1];
        $forgotten = 0;

        Organization::query()->select('id')->chunkById(200, function ($orgs) use ($months, $years, &$forgotten) {
            foreach ($orgs as $org) {
                foreach ($years as $year) {
                    foreach ($months as $month) {
                        $key = sprintf('manager_dashboard_sales_%d_%d_%02d', (int) $org->id, $year, $month);
                        Cache::forget($key);
                        $forgotten++;
                    }
                }
            }
        });

        $this->info("Nightly maintenance complete. Cleared {$forgotten} dashboard cache keys.");

        return self::SUCCESS;
    }
}

