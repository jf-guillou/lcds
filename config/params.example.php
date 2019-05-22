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
    'useSSO' => false,
    'ssoEnvUsername' => 'REDIRECT_REMOTE_USER',
    // LDAP
    'useLdap' => false,
    // ActiveDirectory schema (will use OpenLDAP if false)
    'activeDirectorySchema' => false,
    'ldapOptions' => [
        // 636 for ldaps
        'port' => 389,
        // AD domain controllers or LDAP servers
        'domain_controllers' => ['controller.domain.local'],
        // 'uid=' for OpenLDAP
        'account_prefix' => '',
        // ',ou=users,dc=local' for OpenLDAP
        'account_suffix' => '@local',
        'base_dn' => 'dc=local',
        'admin_username' => 'admin',
        'admin_password' => 'password',
        'use_ssl' => true,
        'use_tls' => true,
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
