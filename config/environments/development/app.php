<?php

use Infuse\Email\Driver\SwiftDriver;

return [
    'app' => [
        'hostname' => 'localhost',
        'port' => '1234',
        'ssl' => false,
    ],
    'database' => [
        'main' => [
            'type' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'name' => 'mydb',
            'user' => 'myuser',
            'password' => 'mypass',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],
    ],
    'email' => [
        'from_email' => 'inspirevive@example.com',
        'driver' => SwiftDriver::class,
        'host' => 'localhost',
        'port' => 1025
    ],
    'logger' => [
        'enabled' => true,
    ]
];