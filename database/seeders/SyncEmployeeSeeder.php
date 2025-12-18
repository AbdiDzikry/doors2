<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\EmployeeApiService;
use Illuminate\Support\Facades\Log;

class SyncEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(EmployeeApiService $employeeApiService): void
    {
        $this->command->info('Starting Employee Sync from API...');

        try {
            $employees = $employeeApiService->fetchEmployees();
            
            $count = 0;
            $total = count($employees);
            
            $this->command->getOutput()->progressStart($total);

            foreach ($employees as $employeeData) {
                // Ignore if NPK is missing
                if (empty($employeeData['EMPLOYEE_NO'])) {
                    continue;
                }

                $employeeApiService->syncEmployee($employeeData);
                $this->command->getOutput()->progressAdvance();
                $count++;
            }

            $this->command->getOutput()->progressFinish();
            $this->command->info("Successfully synced $count employees.");

        } catch (\Exception $e) {
            $this->command->error('Failed to sync employees: ' . $e->getMessage());
            Log::error('Seeder Sync Error: ' . $e->getMessage());
        }
    }
}
