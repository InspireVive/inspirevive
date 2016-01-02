<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\reports;

use app\organizations\models\Organization;
use app\reports\libs\Report;
use app\volunteers\models\Volunteer;

class Controller
{
    use \InjectApp;

    public function makeReport($req, $res)
    {
        $organization = new Organization($req->params('organization'));

        if ($organization->getRoleOfUser($this->app['user']) != Volunteer::ROLE_ADMIN) {
            return $res->setCode(404);
        }

        $type = $req->query('type');
        $start = $req->query('start');
        $end = $req->query('end');

        if (!is_numeric($start)) {
            $start = strtotime($start);
        }
        if (!is_numeric($end)) {
            $end = strtotime($end);
        }

        if ($report = Report::getReport($this->app, $organization, $type, $start, $end)) {
            $report->output($req->query('output'), true, $res);
        } else {
            $res->setCode(404);
        }
    }
}
