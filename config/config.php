<?php
// Central configuration. Keep this file out of version control if it ever
// holds real production credentials.

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'thegrovepk_db',
        'user' => 'thegrovepk_user',
        'pass' => 'Passw903rd',
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'host' => 'mail.thegrove.pk',
        'port' => 465,
        'username' => 'registration@thegrove.pk',
        'password' => 'Passw903rd',
        'encryption' => 'ssl',
        'from_address' => 'registration@thegrove.pk',
        'from_name' => 'The Grove',
        'admin_notify_address' => 'registration@thegrove.pk',
    ],

    // Random string used to sign admin session cookies / CSRF tokens.
    'app_secret' => 'change-this-to-a-long-random-string',
];
