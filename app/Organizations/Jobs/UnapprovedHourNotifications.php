<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Organizations\Jobs;

use App\Organizations\Models\Organization;

/**
 * Notifies organizations about volunteer hours pending approval.
 */
class UnapprovedHourNotifications
{
    public function __invoke($run)
    {
        $n = $this->run();
        $run->writeOutput("Sent $n notifications");
    }

    /**
     * Gets all organizations with unapproved hour notifications.
     *
     * @return \Pulsar\Iterator
     */
    public function getOrganizations()
    {
        return Organization::where('unapproved_hours_notify_count', 0, '>')
            ->all();
    }

    /**
     * Sends unapproved hour notifications.
     *
     * @return int # of notifications sent
     */
    public function run()
    {
        $orgs = $this->getOrganizations();

        $n = 0;
        foreach ($orgs as $org) {
            if ($org->sendUnapprovedNotification()) {
                $n++;
            }
        }

        return $n;
    }
}
