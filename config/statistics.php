<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return [
    'metrics' => [
        'DatabaseSize',
        'DatabaseTables',
        'DatabaseVersion',
        'PhpVersion',
        'SignupsToday',
        'SiteMode',
        'FrameworkVersion',
        'SessionAdapter',
        'SiteStatus',
        'TotalUsers',
    ],
    'dashboard' => [
        'Users' => [
            'TotalUsers',
            'SignupsToday',
        ],
        'Site' => [
            'SiteStatus',
            'PhpVersion',
            'FrameworkVersion',
            'SiteMode',
            'SessionAdapter',
        ],
        'Database' => [
            'DatabaseSize',
            'DatabaseVersion',
            'DatabaseTables',
        ],
    ],
];
