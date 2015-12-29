<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\reports\libs;

use infuse\Inflector;
use App;
use app\organizations\models\Organization;

class Report
{
    public static $availableReports = [
        'hours_by_volunteer' => [
            'id' => 'hours_by_volunteer',
            'name' => 'Total Hours by Volunteer',
            'group' => 'Hours',
        ],
        'hours_detail' => [
            'id' => 'hours_detail',
            'name' => 'Hours Log',
            'group' => 'Hours',
        ],
    ];

    /**
     * Generates a report object.
     *
     * @param App          $app          DI container
     * @param Organization $organization
     * @param string       $type         report type id
     * @param int          $start        start date (unix timestamp)
     * @param int          $end          end date (unix timestamp)
     *
     * @return AbstractReport report object
     */
    public static function getReport(App $app, Organization $organization, $type, $start, $end)
    {
        $type = str_replace('-', ' ', $type);
        $obj = 'app\reports\libs\Reports\\'.Inflector::camelize($type);

        if (class_exists($obj)) {
            return new $obj($app, $organization, $start, $end);
        } else {
            return false;
        }
    }
}
