<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return  [
  'site' => [
    'title' => 'InspireVive',
    'email' => 'hello@example.com',
    'production-level' => false,
    'host-name' => 'example.com',
    'ssl-enabled' => false,
    'salt' => 'REPLACE WITH RANDOM VALUE',
    'time-zone' => 'America/Chicago',
    'language' => 'en',
  ],
  'logger' => [
    'enabled' => true,
  ],
  'database' => [
    'type' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'mydb',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8',
  ],
  'redis' => [
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379,
  ],
  'email' => [
    'from_email' => 'hello@example.com',
    'from_name' => 'InspireVive',
    'type' => 'mandrill',
    'key' => '',
  ],
  'views' => [
    'engine' => 'smarty',
  ],
  'models' => [
    'cache' => [
      'strategies' => [
        'redis',
        'local',
      ],
      'prefix' => 'inspirevive:',
    ],
  ],
  'sessions' => [
    'enabled' => true,
    'lifetime' => 86400,
  ],
  'queue' => [
    'type' => 'synchronous',
    'queues' => [
      'emails',
    ],
    'listeners' => [
      'emails' => [
        ['email\Controller', 'processEmail'], ],
    ],
  ],
  'assets' => [
    'base_url' => '//example.com',
    'cacheExtensions' => ['ico','css','js','gif','jpg','jpeg','png','exe','csv','zip','gz','pdf','html','woff','ttf','eot','svg'],
  ],
  'admin' => [
    'index' => 'statistics',
  ],
  'aws-s3' => [
    'key' => '',
    'secret' => '',
  ],
  'oauth2' => [
    'issuer' => 'http://example.com/api',
    'access_lifetime' => 86400 * 14, // 14 days
  ],
  'modules' => include 'config/modules.php',
  'routes' => include 'config/routes.php',
  'cron' => include 'config/cron.php',
  'statistics' => include 'config/statistics.php',
];
