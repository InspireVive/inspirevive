<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Reports;

use App\Organizations\Models\Organization;
use App\Reports\Libs\Report;
use App\Volunteers\Models\Volunteer;
use Infuse\HasApp;

class Controller
{
    use HasApp;

    public function makeReport($req, $res)
    {
        $organization = Organization::find($req->request('organization'));
        if (!$organization) {
            return $res->setCode(404);
        }

        if ($organization->getRoleOfUser($this->app['user']) != Volunteer::ROLE_ADMIN) {
            return $res->setCode(404);
        }

        $type = $req->request('type');
        $start = $req->request('start');
        $end = $req->request('end');

        if (!is_numeric($start)) {
            $start = strtotime($start);
        }
        if (!is_numeric($end)) {
            $end = strtotime($end);
        }

        if ($report = Report::getReport($this->app, $organization, $type, $start, $end)) {
            $report->output($req->request('output'), true, $res);
        } else {
            $res->setCode(404);
        }
    }
}
