<?php

namespace App\Console\Commands;

use App\Services\EmployeeApiService;
use Illuminate\Console\Command;

class SyncEmployeesFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:employees {--debug : Output verbose debugging info} {--force : Bypass the 5-minute throttle}';

    protected $description = 'Sync employee data from external API';

    public function handle(EmployeeApiService $apiService)
    {
        $debug = $this->option('debug');
        $force = $this->option('force');
        $this->info('Starting employee sync...');
        
        try {
            $result = $apiService->syncAll($force);
            
            if ($result['status'] === 'throttled') {
                $this->warn('Sync skipped: Already performed recently (5min throttle).');
                return Command::SUCCESS;
            }

            $this->info("Sync completed. Synced: {$result['synced']}, Errors: {$result['errors']}");

        } catch (\Exception $e) {
            $this->error('Sync failed (Critical): ' . $e->getMessage());
            if ($debug) {
                $this->warn('Stack Trace:');
                $this->line($e->getTraceAsString());
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
