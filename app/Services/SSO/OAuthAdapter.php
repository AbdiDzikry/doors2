<?php

namespace App\Services\SSO;

use Illuminate\Support\Facades\Http;

/**
 * OAuth Adapter
 * Support OAuth 2.0 & OpenID Connect
 */
class OAuthAdapter implements SSOAdapterInterface
{
    protected $config;
    
    public function __construct($config)
    {
        $this->config = $config['oauth'];
    }
    
    public function getLoginUrl($state)
    {
        $params = http_build_query([
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', $this->config['scopes']),
            'state' => $state,
        ]);
        
        return $this->config['authorization_url'] . '?' . $params;
    }
    
    public function getUserData($request)
    {
        $code = $request->input('code');
        
        // Exchange code for token
        $tokenResponse = Http::asForm()->post($this->config['token_url'], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'],
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
        ]);
        
        if (!$tokenResponse->successful()) {
            throw new \Exception('Failed to exchange code for token');
        }
        
        $token = $tokenResponse->json()['access_token'];
        
        // Get user info
        $userResponse = Http::withToken($token)->get($this->config['user_info_url']);
        
        if (!$userResponse->successful()) {
            throw new \Exception('Failed to get user info');
        }
        
        return $userResponse->json();
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
