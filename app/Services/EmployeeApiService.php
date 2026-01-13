<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeApiService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.employee_api.key', '');
        $this->apiUrl = config('services.employee_api.url', 'https://msa-be.dharmagroup.co.id/api/data/company');
    }

    /**
     * Fetch employees from the external API.
     *
     * @return array
     * @throws \Exception
     */
    public function fetchEmployees(): array
    {
        try {
            Log::info('Fetching employees from API...', ['url' => $this->apiUrl]);

            $response = Http::withoutVerifying()->withHeaders([
                'x-api-key' => $this->apiKey
            ])->get($this->apiUrl, ['company' => 'dpm']);

            if ($response->successful()) {
                $data = $response->json();
                // Check if data is wrapped in 'data' key or direct array
                return $data['data'] ?? $data;
            }

            Log::error('API Request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception('API request failed with status: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Employee Sync Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function syncAll(bool $force = false): array
    {
        // Throttle full sync to once every 5 minutes during on-demand requests
        $lockKey = 'employee_sync_lock';
        if (!$force && \Illuminate\Support\Facades\Cache::has($lockKey)) {
            Log::info('Employee sync skipped (Throttled).');
            return ['synced' => 0, 'errors' => 0, 'status' => 'throttled'];
        }

        if (!$force) {
            \Illuminate\Support\Facades\Cache::put($lockKey, true, now()->addMinutes(5));
        }

        try {
            $employees = $this->fetchEmployees();
            $synced = 0;
            $errors = 0;

            foreach ($employees as $employeeData) {
                try {
                    $this->syncEmployee($employeeData);
                    $synced++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }

            return [
                'synced' => $synced,
                'errors' => $errors,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            Log::error('Full Sync Failed: ' . $e->getMessage());
            return ['synced' => 0, 'errors' => 0, 'status' => 'failed'];
        }
    }

    public function syncEmployee(array $apiData): User
    {
        // Normalize keys to uppercase to handle potential case variance from API
        $data = array_change_key_case($apiData, CASE_UPPER);

        // Required key check
        if (!isset($data['EMPLOYEE_NO'])) {
             Log::error('Employee Sync Error: Missing EMPLOYEE_NO key', ['data' => $data]);
             throw new \Exception('Missing EMPLOYEE_NO key in API data');
        }

        $npk = $data['EMPLOYEE_NO'];
        $name = $data['EMPLOYEE_NAME'] ?? 'Unknown Employee';

        // Check if user exists first to decide on password
        $user = User::where('npk', $npk)->first();

        if ($user) {
            // Update existing user (WITHOUT touching password)
            $user->update([
                'name' => $name,
                'division' => $data['DIVISION'] ?? null,
                'department' => $data['DEPARTMENT'] ?? null,
                'organization_unit' => $data['ORGANIZATION_UNIT'] ?? null,
                'job_family' => $data['JOB_FAMILY'] ?? null,
                'position' => $data['JOB_FAMILY'] ?? null, // Map JOB_FAMILY to position
                'last_synced_at' => now(),
            ]);
        } else {
            // Create new user (WITH default password = NPK)
            $user = User::create([
                'npk' => $npk,
                'name' => $name,
                'password' => bcrypt($npk), // Default password is NPK
                'division' => $data['DIVISION'] ?? null,
                'department' => $data['DEPARTMENT'] ?? null,
                'organization_unit' => $data['ORGANIZATION_UNIT'] ?? null,
                'job_family' => $data['JOB_FAMILY'] ?? null,
                'position' => $data['JOB_FAMILY'] ?? null, // Map JOB_FAMILY to position
                'last_synced_at' => now(),
            ]);
        }

        // Assign default role "karyawan" if user doesn't have any roles
        if ($user->roles()->count() === 0) {
            $user->assignRole('karyawan');
        }

        return $user;
    }
}
