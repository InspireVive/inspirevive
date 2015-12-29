<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return [
    'middleware' => [
        'logstash',
        'auth',
        'oauth2',
        'admin',
        'email',
        'facebook',
        'instagram',
        'twitter',
        'iron',
    ],
    'all' => [
        // external
        'auth',
        'admin',
        'api',
        'cron',
        'email',
        'statistics',
        'iron',
        'oauth2',
        'facebook',
        'instagram',
        'twitter',
        // inspirevive
        'organizations',
        'pages',
        'reports',
        'users',
        'volunteers',
    ],
];
