<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Reports\Libs;

use App\Organizations\Models\Organization;
use Infuse\Application;
use ICanBoogie\Inflector;

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
     * @param Application          $app          DI container
     * @param Organization $organization
     * @param string       $type         report type id
     * @param int          $start        start date (unix timestamp)
     * @param int          $end          end date (unix timestamp)
     *
     * @throws \InvalidArgumentException
     *
     * @return \App\Reports\Libs\Reports\AbstractReport report object
     */
    public static function getReport(Application $app, Organization $organization, $type, $start, $end)
    {
        $type = str_replace('-', ' ', $type);
        $inflector = Inflector::get();
        $obj = 'App\Reports\Libs\Reports\\'.$inflector->camelize($type);

        if (!class_exists($obj)) {
            throw new \InvalidArgumentException('Unrecognized report type: '.$type);
        }

        return new $obj($app, $organization, $start, $end);
    }
}
