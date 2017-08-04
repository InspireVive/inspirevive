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
        'auth',
        'oauth2',
        'admin',
        'email',
    ],
    'all' => [
        // external
        'auth',
        'admin',
        'api',
        'cron',
        'email',
        'statistics',
        'oauth2',
        // inspirevive
        'organizations',
        'pages',
        'reports',
        'users',
        'volunteers',
    ],
];
