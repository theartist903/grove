<?php
// Central configuration. Keep this file out of version control if it ever
// holds real production credentials.

return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'thegrove',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'host' => 'sandbox.smtp.mailtrap.io',
        'port' => 2525,
        'username' => 'a8ac0d343aa7f3',
        'password' => '1e8ff4ac452414',
        'encryption' => 'tls',
        'from_address' => 'registration@thegrove.pk',
        'from_name' => 'The Grove',
        'admin_notify_address' => 'registration@thegrove.pk',
    ],

    // Random string used to sign admin session cookies / CSRF tokens.
    'app_secret' => 'change-this-to-a-long-random-string',
];
