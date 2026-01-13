<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    | Universal SSO configuration yang support multiple protocols
    */

    'enabled' => env('SSO_ENABLED', false),
    
    'protocol' => env('SSO_PROTOCOL', 'auto'), // auto, oauth, saml2, custom
    
    'base_url' => env('SSO_BASE_URL', 'https://sso.dharmap.com'),
    
    'token_verify_url' => env('SSO_TOKEN_VERIFY_URL'),
    
    /*
    |--------------------------------------------------------------------------
    | Auto Provisioning
    |--------------------------------------------------------------------------
    */
    
    'auto_provision' => env('SSO_ENABLE_AUTO_PROVISION', true),
    'auto_update' => env('SSO_ENABLE_AUTO_UPDATE', true),
    'default_role' => env('SSO_DEFAULT_ROLE', 'Karyawan'),
    
    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    */
    
    'oauth' => [
        'client_id' => env('SSO_CLIENT_ID'),
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'redirect_uri' => env('SSO_REDIRECT_URI', env('APP_URL') . '/auth/sso/callback'),
        'authorization_url' => env('SSO_AUTHORIZATION_URL'),
        'token_url' => env('SSO_TOKEN_URL'),
        'user_info_url' => env('SSO_USER_INFO_URL'),
        'logout_url' => env('SSO_LOGOUT_URL'),
        'scopes' => ['openid', 'profile', 'email'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | SAML Configuration
    |--------------------------------------------------------------------------
    */
    
    'saml' => [
        'idp_entity_id' => env('SAML2_IDP_ENTITYID'),
        'idp_sso_url' => env('SAML2_IDP_SSO_URL'),
        'idp_slo_url' => env('SAML2_IDP_SLO_URL'),
        'idp_x509' => env('SAML2_IDP_x509'),
        'sp_entity_id' => env('SAML2_SP_ENTITYID', env('APP_URL')),
        'sp_acs_url' => env('SAML2_SP_ACS_URL', env('APP_URL') . '/saml2/acs'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Custom SSO Configuration
    |--------------------------------------------------------------------------
    */
    
    'custom' => [
        'login_url' => env('SSO_LOGIN_URL'),
        'callback_url' => env('SSO_CALLBACK_URL', env('APP_URL') . '/auth/sso/callback'),
        'verify_url' => env('SSO_VERIFY_URL'),
        'logout_url' => env('SSO_LOGOUT_URL'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Attribute Mapping
    |--------------------------------------------------------------------------
    | Map SSO response attributes ke database fields
    */
    
    'attributes' => [
        'npk' => env('SSO_ATTR_NPK', 'npk'),
        'name' => env('SSO_ATTR_NAME', 'full_name'), // Changed default to 'full_name' per verification
        'email' => env('SSO_ATTR_EMAIL', 'email'),
        'department' => env('SSO_ATTR_DEPARTMENT', 'department'),
        'job_title' => env('SSO_ATTR_JOB_TITLE', 'position'), // Changed default to 'position' per verification
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Role Mapping
    |--------------------------------------------------------------------------
    | Map department/job_title dari SSO ke role aplikasi
    */
    
    'role_mapping' => [
        'department' => [
            'IT' => 'Admin',
            'HR' => 'Admin',
            'Reception' => 'Resepsionis',
        ],
        'job_title' => [
            'Manager' => 'Admin',
            'Supervisor' => 'Admin',
        ],
    ],
];
