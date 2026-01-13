<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SSO\SSOService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    protected $ssoService;
    
    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }
    
    /**
     * Redirect user ke SSO untuk login
     */
    public function login()
    {
        if (!config('sso.enabled')) {
            return redirect()->route('login')
                ->with('error', 'SSO is currently disabled. Please use manual login.');
        }
        
        try {
            $loginUrl = $this->ssoService->getLoginUrl();
            return redirect()->away($loginUrl);
        } catch (\Exception $e) {
            \Log::error('SSO Redirect Failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Failed to connect to SSO. Please try again or use manual login.');
        }
    }
    
    /**
     * Handle callback dari SSO
     */
    public function callback(Request $request)
    {
        if (!config('sso.enabled')) {
            return redirect()->route('login')
                ->with('error', 'SSO is disabled.');
        }
        
        try {
            // Direct Token Handling (IdP-Initiated)
            $token = $request->input('sso_token') 
                  ?? $request->input('token')
                  ?? $request->input('code');

            if ($token) {
                 // Verify manually if token is present directly
                 $verifyUrl = config('sso.token_verify_url');
                 if ($verifyUrl) {
                     $response = \Illuminate\Support\Facades\Http::withToken($token)->get($verifyUrl);
                     if ($response->successful()) {
                         $ssoUserData = $response->json();
                         // Normalize
                         $ssoUserData['npk'] = data_get($ssoUserData, config('sso.attributes.npk', 'npk'));
                     }
                 }
            }

            // Fallback to Service (SP-Initiated / OAuth Code Exchange)
            if (empty($ssoUserData)) {
                $ssoUserData = $this->ssoService->handleCallback($request);
            }
            
            // Validate NPK
            if (empty($ssoUserData['npk'])) {
                throw new \Exception('NPK not found in SSO response');
            }
            
            // Find or create user
            $user = $this->findOrCreateUser($ssoUserData);
            
            // Update user data jika auto-update enabled
            if (config('sso.auto_update')) {
                $this->updateUserFromSSO($user, $ssoUserData);
            }
            
            // Update last SSO login timestamp
            $user->update([
                'last_sso_login' => now(),
            ]);
            
            // Login user
            Auth::login($user);
            
            // Redirect ke dashboard berdasarkan role
            return $this->redirectBasedOnRole($user);
            
        } catch (\Exception $e) {
            \Log::error('SSO Callback Failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'SSO login failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Find or create user berdasarkan NPK
     */
    protected function findOrCreateUser($ssoUserData)
    {
        $user = User::where('npk', $ssoUserData['npk'])->first();
        
        if ($user) {
            return $user;
        }
        
        // User tidak ada dan auto-provision disabled
        if (!config('sso.auto_provision')) {
            throw new \Exception('User not found and auto-provisioning is disabled. Please contact administrator.');
        }
        
        // Auto-create user
        $user = User::create([
            'npk' => $ssoUserData['npk'],
            'name' => $ssoUserData['name'] ?? 'User ' . $ssoUserData['npk'],
            'email' => $ssoUserData['email'] ?? $ssoUserData['npk'] . '@dharmap.com',
            'password' => Hash::make(Str::random(32)), // Random password (tidak dipakai)
            'department_id' => $this->findOrCreateDepartment($ssoUserData['department'] ?? null),
            'job_title' => $ssoUserData['job_title'] ?? null,
            'is_active' => true,
            'sso_id' => $ssoUserData['npk'],
            'sso_provider' => 'dharmap-sso',
        ]);
        
        // Assign role berdasarkan department/job_title
        $role = $this->ssoService->determineRole(
            $ssoUserData['department'] ?? null,
            $ssoUserData['job_title'] ?? null
        );
        
        $user->assignRole($role);
        
        \Log::info('Auto-provisioned new user from SSO', [
            'npk' => $user->npk,
            'name' => $user->name,
            'role' => $role,
        ]);
        
        return $user;
    }
    
    /**
     * Update user data dari SSO
     */
    protected function updateUserFromSSO($user, $ssoUserData)
    {
        $user->update([
            'name' => $ssoUserData['name'] ?? $user->name,
            'email' => $ssoUserData['email'] ?? $user->email,
            'department_id' => $this->findOrCreateDepartment($ssoUserData['department'] ?? null) ?? $user->department_id,
            'job_title' => $ssoUserData['job_title'] ?? $user->job_title,
        ]);
    }
    
    /**
     * Find or create department
     */
    protected function findOrCreateDepartment($departmentName)
    {
        if (empty($departmentName)) {
            return null;
        }
        
        $department = \App\Models\Department::firstOrCreate(
            ['name' => $departmentName],
            ['name' => $departmentName]
        );
        
        return $department->id;
    }
    
    /**
     * Redirect berdasarkan role
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('dashboard.superadmin');
        }
        
        if ($user->hasRole('Admin')) {
            return redirect()->route('dashboard.admin');
        }
        
        if ($user->hasRole('Resepsionis')) {
            return redirect()->route('dashboard.receptionist');
        }
        
        return redirect()->route('dashboard.karyawan');
    }
    
    /**
     * Logout dari SSO
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect ke SSO logout jika enabled
        if (config('sso.enabled')) {
            $logoutUrl = $this->ssoService->getLogoutUrl();
            return redirect()->away($logoutUrl);
        }
        
        return redirect('/');
    }
}
