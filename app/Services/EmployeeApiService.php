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

    /**
     * Sync a single employee data to the User model.
     *
     * @param array $apiData
     * @return User
     */
    public function syncEmployee(array $apiData): User
    {
        // Default password is NPK
        $defaultPassword = $apiData['EMPLOYEE_NO'];

        // Check if user exists first to decide on password
        $user = User::where('npk', $apiData['EMPLOYEE_NO'])->first();

        if ($user) {
            // Update existing user (WITHOUT touching password)
            $user->update([
                'name' => $apiData['EMPLOYEE_NAME'],
                'division' => $apiData['DIVISION'] ?? null,
                'department' => $apiData['DEPARTMENT'] ?? null,
                'organization_unit' => $apiData['ORGANIZATION_UNIT'] ?? null,
                'job_family' => $apiData['JOB_FAMILY'] ?? null,
                'position' => $apiData['JOB_FAMILY'] ?? null, // Map JOB_FAMILY to position
                'last_synced_at' => now(),
            ]);
        } else {
            // Create new user (WITH default password = NPK)
            $user = User::create([
                'npk' => $apiData['EMPLOYEE_NO'],
                'name' => $apiData['EMPLOYEE_NAME'],
                'password' => bcrypt($apiData['EMPLOYEE_NO']), // Default password is NPK
                'division' => $apiData['DIVISION'] ?? null,
                'department' => $apiData['DEPARTMENT'] ?? null,
                'organization_unit' => $apiData['ORGANIZATION_UNIT'] ?? null,
                'job_family' => $apiData['JOB_FAMILY'] ?? null,
                'position' => $apiData['JOB_FAMILY'] ?? null, // Map JOB_FAMILY to position
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
