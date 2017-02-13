<?php

return [
    // Supported languages 'en-US', 'fr-FR'
    'language' => 'en-US',
    // Pretty urls, see README to configure
    'prettyUrl' => false,
    // Autofilled with composer install
    'cookieValidationKey' => '',
    // Outgoing proxy
    'proxy' => false,
    // Authentication
    'useKerberos' => false,
    'kerberosPrincipalVar' => 'REDIRECT_REMOTE_USER',
    'useLdap' => false,
    'ldapOptions' => [
        'port' => 389,
        'domain_controllers' => ['controller.domain.local'],
        'account_suffix' => '@local',
        'base_dn' => 'dc=local',
        'admin_username' => 'admin',
        'admin_password' => 'password',
        'use_ssl' => true,
        'use_tls' => true,
        'use_sso' => true,
    ],
    // Cookie duration in seconds
    'cookieDuration' => 60 * 60 * 24 * 7,

    // Content types
    // Weather widget
    'weather' => [ // Get an API key at https://darksky.net/dev
        'language' => 'en',
        'units' => 'us',
        'apikey' => '',
        'withSummary' => false,
    ],
];
