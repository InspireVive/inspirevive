<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\organizations;

use app\organizations\models\Organization;

class Controller
{
    public static $properties = [
        'models' => [
            'Organization',
        ],
    ];

    public static $scaffoldAdmin;

    public function cron($command)
    {
        if ($command == 'unapproved-hour-notifications') {
            $n = Organization::processUnapprovedNotifications();

            echo "--- Sent $n notifications\n";

            return true;
        }
    }
}
