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
    protected $signature = 'sync:employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync employee data from external API';

    /**
     * Execute the console command.
     */
    public function handle(EmployeeApiService $apiService)
    {
        $this->info('Starting employee sync...');

        try {
            $employees = $apiService->fetchEmployees();
            $count = count($employees);
            $this->info("Found {$count} employees. Processing...");

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $synced = 0;
            $errors = 0;

            foreach ($employees as $employeeData) {
                try {
                    $apiService->syncEmployee($employeeData);
                    $synced++;
                } catch (\Exception $e) {
                    $errors++;
                    // Log specific error if needed, but keep bar moving
                    // Log::error("Failed to sync NPK {$employeeData['EMPLOYEE_NO']}: " . $e->getMessage());
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Sync completed. Synced: {$synced}, Errors: {$errors}");

        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
