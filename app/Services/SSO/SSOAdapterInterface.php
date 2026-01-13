<?php

namespace App\Services\SSO;

interface SSOAdapterInterface
{
    /**
     * Get SSO login URL
     */
    public function getLoginUrl($state);
    
    /**
     * Get user data dari SSO response
     */
    public function getUserData($request);
    
    /**
     * Get logout URL  
     */
    public function getLogoutUrl();
    
    /**
     * Apakah adapter ini memerlukan state validation?
     */
    public function requiresStateValidation();
}
