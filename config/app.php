<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Infuse\Auth\Libs\RateLimiter\RedisRateLimiter;
use Infuse\Queue\Driver\SynchronousDriver;
use Infuse\ViewEngine\Twig;
use Pulsar\Driver\DatabaseDriver;

return  [
    'app' => [
        'title' => 'InspireVive',
        'language' => 'en',
    ],
    'auth' => [
        'loginRateLimiter' => RedisRateLimiter::class
    ],
    'assets' => [
        'base_url' => '//inspirevive.localhost:1234',
        'cacheExtensions' => ['ico','css','js','gif','jpg','jpeg','png','exe','csv','zip','gz','pdf','html','woff','ttf','eot','svg' ],
    ],
    'cache' => [
        'namespace' => 'inspirevive',
    ],
    'i18n' => [
        'locale' => 'en',
    ],
    'logger' => [
        'enabled' => true,
    ],
    'models' => [
        'driver' => DatabaseDriver::class,
    ],
    'queue' => [
        'driver' => SynchronousDriver::class,
        'queues' => [
            'emails',
        ],
    ],
    'redis' => [
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
    ],
    'sessions' => [
        'enabled' => true,
        'lifetime' => 86400,
    ],
    'views' => [
        'engine' => Twig::class,
        'twigConfig' => [
            'auto_reload' => true,
        ],
    ],
];
