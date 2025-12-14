<?php

return [
    'db' => [
        'driver'   => 'mysql',
        'host'     => getenv('MYSQLHOST'),
        'port'     => getenv('MYSQLPORT'),
        'database' => getenv('MYSQLDATABASE'),
        'username' => getenv('MYSQLUSER'),
        'password' => getenv('MYSQLPASSWORD'),
    ]
];
