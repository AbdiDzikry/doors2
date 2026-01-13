<?php

namespace App\Services\SSO;

use Illuminate\Support\Facades\Http;

/**
 * Custom SSO Adapter
 * Support implementasi SSO custom perusahaan
 */
class CustomAdapter implements SSOAdapterInterface
{
    protected $config;
    
    public function __construct($config)
    {
        $this->config = $config['custom'];
    }
    
    public function getLoginUrl($state)
    {
        // Build login URL dengan state
        $params = http_build_query([
            'redirect_uri' => $this->config['callback_url'],
            'state' => $state,
            'app' => 'doors', // Identifier untuk aplikasi kita
        ]);
        
        return $this->config['login_url'] . '?' . $params;
    }
    
    public function getUserData($request)
    {
        // Coba beberapa format yang umum digunakan
        
        // Format 1: Token di query parameter
        if ($token = $request->input('token')) {
            return $this->getUserFromToken($token);
        }
        
        // Format 2: Code yang perlu di-verify
        if ($code = $request->input('code')) {
            return $this->getUserFromCode($code);
        }
        
        // Format 3: Direct user data di POST
        if ($request->has('npk')) {
            return $request->only(['npk', 'name', 'email', 'department', 'job_title']);
        }
        
        throw new \Exception('Unable to extract user data from SSO response');
    }
    
    protected function getUserFromToken($token)
    {
        // Verify token dengan SSO server
        $response = Http::withToken($token)->get($this->config['verify_url']);
        
        if (!$response->successful()) {
            throw new \Exception('Invalid SSO token');
        }
        
        return $response->json();
    }
    
    protected function getUserFromCode($code)
    {
        // Verify code dengan SSO server
        $response = Http::post($this->config['verify_url'], [
            'code' => $code,
            'app' => 'doors',
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Invalid SSO code');
        }
        
        return $response->json();
    }
    
    public function getLogoutUrl()
    {
        return $this->config['logout_url'] ?? config('sso.base_url') . '/logout';
    }
    
    public function requiresStateValidation()
    {
        return true;
    }
}
