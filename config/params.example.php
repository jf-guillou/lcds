<?php

return [
    'adminEmail' => 'admin@example.com',
    'cookieValidationKey' => '',
    'debugAllowedIPs' => null,
    'proxy' => false,
    // Auth
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
    'cookieDuration' => 60 * 60 * 24 * 7,
    'language' => 'en-US',
    // Content types
    'agenda' => [
        'width' => 1260,
        'height' => 880,
        'calendarTimezone' => 'UTC',
        'displayTeachers' => false,
    ],
    'weather' => [ // https://darksky.net/dev
        'language' => 'en',
        'units' => 'us',
        'apikey' => '',
        'withSummary' => false,
    ],
];
