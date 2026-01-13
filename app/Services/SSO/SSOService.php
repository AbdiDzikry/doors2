<?php

namespace App\Services\SSO;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Universal SSO Service
 * 
 * Support multiple SSO protocols dengan auto-detection
 */
class SSOService
{
    protected $config;
    protected $adapter;
    
    public function __construct()
    {
        $this->config = config('sso');
        $this->adapter = $this->getAdapter();
    }
    
    /**
     * Auto-detect dan return adapter yang sesuai
     */
    protected function getAdapter()
    {
        $protocol = $this->config['protocol'];
        
        if ($protocol === 'auto') {
            // Auto-detect berdasarkan config yang tersedia
            if (!empty($this->config['oauth']['client_id'])) {
                $protocol = 'oauth';
            } elseif (!empty($this->config['saml']['idp_entity_id'])) {
                $protocol = 'saml';
            } else {
                $protocol = 'custom';
            }
        }
        
        return match($protocol) {
            'oauth' => new OAuthAdapter($this->config),
            'saml2', 'saml' => new SamlAdapter($this->config),
            'custom' => new CustomAdapter($this->config),
            default => throw new \Exception("Unsupported SSO protocol: {$protocol}"),
        };
    }
    
    /**
     * Generate redirect URL ke SSO
     */
    public function getLoginUrl($state = null)
    {
        $state = $state ?? \Str::random(40);
        session(['sso_state' => $state]);
        
        return $this->adapter->getLoginUrl($state);
    }
    
    /**
     * Handle callback dari SSO
     */
    public function handleCallback($request)
    {
        try {
            // Validate state (CSRF protection)
            if ($this->adapter->requiresStateValidation()) {
                $this->validateState($request);
            }
            
            // Get user data dari SSO
            $ssoUser = $this->adapter->getUserData($request);
            
            // Normalize data ke format standar
            $userData = $this->normalizeUserData($ssoUser);
            
            // Log SSO login
            Log::channel('sso')->info('SSO Login Success', [
                'npk' => $userData['npk'],
                'protocol' => $this->config['protocol'],
                'timestamp' => now(),
            ]);
            
            return $userData;
            
        } catch (\Exception $e) {
            Log::channel('sso')->error('SSO Login Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Validate state parameter (CSRF protection)
     */
    protected function validateState($request)
    {
        $requestState = $request->input('state');
        $sessionState = session('sso_state');
        
        if (empty($requestState) || $requestState !== $sessionState) {
            throw new \Exception('Invalid state parameter. Possible CSRF attack.');
        }
        
        session()->forget('sso_state');
    }
    
    /**
     * Normalize user data dari berbagai format SSO
     */
    protected function normalizeUserData($ssoUser)
    {
        $attrMap = $this->config['attributes'];
        
        return [
            'npk' => data_get($ssoUser, $attrMap['npk']),
            'name' => data_get($ssoUser, $attrMap['name']),
            'email' => data_get($ssoUser, $attrMap['email']),
            'department' => data_get($ssoUser, $attrMap['department']),
            'job_title' => data_get($ssoUser, $attrMap['job_title']),
            'raw_data' => $ssoUser, // Simpan raw data untuk debugging
        ];
    }
    
    /**
     * Get logout URL
     */
    public function getLogoutUrl()
    {
        return $this->adapter->getLogoutUrl();
    }
    
    /**
     * Determine role berdasarkan department/job_title
     */
    public function determineRole($department, $jobTitle)
    {
        $roleMapping = $this->config['role_mapping'];
        
        // Cek department mapping
        if (isset($roleMapping['department'][$department])) {
            return $roleMapping['department'][$department];
        }
        
        // Cek job title mapping
        if (isset($roleMapping['job_title'][$jobTitle])) {
            return $roleMapping['job_title'][$jobTitle];
        }
        
        // Default role
        return $this->config['default_role'];
    }
}
