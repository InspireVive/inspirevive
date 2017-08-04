<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return [
    [
        'module' => 'organizations',
        'command' => 'unapproved-hour-notifications',
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'auth',
        'command' => 'garbage-collection',
        'minute' => '30',
        'hour' => '0',
        'day' => '1',
        'month' => '*',
        'week' => '*',
        'expires' => 3600, // 1 hour
    ],
];
