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
        // inspirevive
        'organizations',
        'pages',
        'reports',
        'users',
        'volunteers',
    ],
];
