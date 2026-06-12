<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckAlerts extends Command
{
    protected $signature = 'alerts:check {--company= : Company ID to check}';
    protected $description = 'Check all conditions and generate alerts';

    public function handle(AlertService $alertService): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;

        $this->info('Running alert checks...');

        $results = $alertService->runAllChecks($companyId);

        $total = 0;
        foreach ($results as $cid => $checks) {
            $this->line("Company #{$cid}:");
            foreach ($checks as $type => $count) {
                if ($count > 0) {
                    $this->line("  - {$type}: {$count} alert(s)");
                    $total += $count;
                }
            }
        }

        $this->info("Done. {$total} alert(s) generated.");

        return Command::SUCCESS;
    }
}
