<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return [
    'modules' => [
        'migrations' => [
            // external
            'Auth',
            'Cron',
            // inspirevive
            'Organizations',
            'Users',
            'Volunteers'
        ],
        'migrationPaths' => [
            'Auth' => 'vendor/infuse/auth/src/migrations',
            'Cron' => 'vendor/infuse/cron/src/migrations',
        ],
    ]
];
