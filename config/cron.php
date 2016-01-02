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
    [
        'module' => 'twitter',
        'command' => 'refresh-profiles',
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'twitter',
        'command' => 'refresh-profiles',
        'minute' => '15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'twitter',
        'command' => 'refresh-profiles',
        'minute' => '30',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'twitter',
        'command' => 'refresh-profiles',
        'minute' => '45',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'facebook',
        'command' => 'refresh-profiles',
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'facebook',
        'command' => 'refresh-profiles',
        'minute' => '15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'facebook',
        'command' => 'refresh-profiles',
        'minute' => '30',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'facebook',
        'command' => 'refresh-profiles',
        'minute' => '45',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'instagram',
        'command' => 'refresh-profiles',
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'instagram',
        'command' => 'refresh-profiles',
        'minute' => '15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'instagram',
        'command' => 'refresh-profiles',
        'minute' => '30',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'instagram',
        'command' => 'refresh-profiles',
        'minute' => '45',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 300, // 5 minutes
    ],
    [
        'module' => 'statistics',
        'command' => 'capture-metrics',
        'minute' => '10',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 3600, // 1 hour
    ],
];
