<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SSO Token Middleware
 * 
 * Automatically authenticate user jika token SSO valid
 * Support IdP-initiated flow (user sudah login di SSO Portal)
 */
class SSOTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Skip jika user sudah login
        if (Auth::check()) {
            return $next($request);
        }
        
        // Skip jika SSO disabled
        if (!config('sso.enabled')) {
            return $next($request);
        }
        
        // Cek token SSO di berbagai lokasi
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $next($request);
        }
        
        try {
            // Verify token dengan SSO server
            $userData = $this->verifyToken($token);
            
            if (!$userData || empty($userData['npk'])) {
                Log::warning('Invalid SSO token', ['token' => substr($token, 0, 10) . '...']);
                return $next($request);
            }
            
            // Find or create user
            $user = $this->findOrCreateUser($userData);
            
            // Login user
            Auth::login($user);
            
            Log::info('Auto-login via SSO token', [
                'npk' => $user->npk,
                'name' => $user->name,
            ]);
            
        } catch (\Exception $e) {
            Log::error('SSO Token verification failed', [
                'error' => $e->getMessage(),
            ]);
        }
        
        return $next($request);
    }
    
    /**
     * Extract token dari request
     */
    protected function extractToken($request)
    {
        // Format 1: Token di query parameter (?sso_token=xxx)
        if ($token = $request->query('sso_token')) {
            return $token;
        }
        
        // Format 2: Token di query parameter (?token=xxx)
        if ($token = $request->query('token')) {
            return $token;
        }
        
        // Format 3: Token di header (Authorization: Bearer xxx)
        if ($token = $request->bearerToken()) {
            return $token;
        }
        
        // Format 4: Token di header custom (X-SSO-Token)
        if ($token = $request->header('X-SSO-Token')) {
            return $token;
        }
        
        // Format 5: Token di cookie
        if ($token = $request->cookie('sso_token')) {
            return $token;
        }
        
        return null;
    }
    
    /**
     * Verify token dengan SSO server
     */
    protected function verifyToken($token)
    {
        $verifyUrl = config('sso.token_verify_url') ?? config('sso.base_url') . '/api/verify';
        
        $response = Http::timeout(5)
            ->withToken($token)
            ->get($verifyUrl);
        
        if (!$response->successful()) {
            return null;
        }
        
        $data = $response->json();
        
        // Normalize data
        return [
            'npk' => data_get($data, config('sso.attributes.npk')),
            'name' => data_get($data, config('sso.attributes.name')),
            'email' => data_get($data, config('sso.attributes.email')),
            'department' => data_get($data, config('sso.attributes.department')),
            'job_title' => data_get($data, config('sso.attributes.job_title')),
        ];
    }
    
    /**
     * Find or create user
     */
    protected function findOrCreateUser($userData)
    {
        $user = User::where('npk', $userData['npk'])->first();
        
        if ($user) {
            // Update user data
            if (config('sso.auto_update')) {
                $user->update([
                    'name' => $userData['name'] ?? $user->name,
                    'email' => $userData['email'] ?? $user->email,
                    'last_sso_login' => now(),
                ]);
            }
            return $user;
        }
        
        // Auto-provision if enabled
        if (!config('sso.auto_provision')) {
            throw new \Exception('User not found and auto-provisioning is disabled');
        }
        
        // Create new user
        $user = User::create([
            'npk' => $userData['npk'],
            'name' => $userData['name'] ?? 'User ' . $userData['npk'],
            'email' => $userData['email'] ?? $userData['npk'] . '@dharmap.com',
            'password' => Hash::make(Str::random(32)),
            'is_active' => true,
            'sso_id' => $userData['npk'],
            'sso_provider' => 'dharmap-sso',
            'last_sso_login' => now(),
        ]);
        
        // Assign default role
        $user->assignRole(config('sso.default_role', 'Karyawan'));
        
        return $user;
    }
}
