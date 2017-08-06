<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Infuse\Auth\Libs\Storage\InMemoryStorage;
use Infuse\Email\Driver\NullDriver;

return [
    'app' => [
        'hostname' => 'localhost',
        'port' => '1234',
        'ssl' => false,
    ],
    'auth' => [
        'storage' => InMemoryStorage::class,
    ],
    'database' => [
        'main' => [
            'type' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'name' => 'test',
            'user' => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],
    ],
    'email' => [
        'driver' => NullDriver::class
    ],
    'logger' => [
        'enabled' => false,
    ],
];