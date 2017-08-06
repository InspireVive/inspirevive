<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Organizations\Jobs\UnapprovedHourNotifications;
use Infuse\Auth\Jobs\GarbageCollection;

return [
    'cron' => [
        [
            'id' => 'organizations:unapprovedHourNotifications',
            'class' => UnapprovedHourNotifications::class,
            'minute' => '0',
            'expires' => 300, // 5 minutes
        ],
        [
            'id' => 'auth:garbageCollection',
            'class' => GarbageCollection::class,
            'minute' => '30',
            'hour' => '0',
            'day' => '1',
            'expires' => 3600, // 5 minutes
        ],
    ]
];
