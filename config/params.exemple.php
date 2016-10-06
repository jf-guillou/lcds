<?php

return [
    'adminEmail' => 'admin@example.com',
    'cookieValidationKey' => 'DMod58d6E6qs0cqpOdqecv38mnjgqIOc',
    'debugAllowedIPs' => null,
    'proxy' => false,
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
    ],
    'cookieDuration' => 60 * 60 * 24 * 7,
    'language' => 'en-US',
];
