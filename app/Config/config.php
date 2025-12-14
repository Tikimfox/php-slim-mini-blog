<?php

return [
    'db' => [
        'driver'   => 'mysql',
        'host'     => $_ENV['MYSQLHOST'] ?? $_SERVER['MYSQLHOST'] ?? 'db',
        'port'     => $_ENV['MYSQLPORT'] ?? $_SERVER['MYSQLPORT'] ?? '3306',
        'database' => $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? 'blog_db',
        'username' => $_ENV['MYSQLUSER'] ?? $_SERVER['MYSQLUSER'] ?? 'root',
        'password' => $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? 'secret',

    ]
];
