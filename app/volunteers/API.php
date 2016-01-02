<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\volunteers;

use app\api\libs\ApiRoute;
use app\organizations\models\Organization;
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerPlace;

class API
{
    use \InjectApp;

    public function apiNotifications($req, $res)
    {
        $app = $this->app;

        $route = new ApiRoute();
        $route->addQueryParams([
                'model' => 'app\organizations\models\Organization',
                'model_id' => $req->params('id'), ])
              ->addParseSteps([
                'parseRequireJson',
                'parseModelFindOneParameters', ])
              ->addQueryStep('queryModelFindOne')
              ->addTransformSteps([
                function (&$result, $route) use ($app) {
                    $vOrg = $result->volunteerOrganization();

                    if (!$vOrg) {
                        $route->getResponse()->setCode(404);

                        return false;
                    }

                    if (!$vOrg->can('admin', $app['user'])) {
                        $route->getResponse()->setCode(403);

                        return false;
                    }

                    $result = [
                        'unapproved' => [
                            'volunteers' => $vOrg->numUnapprovedVolunteers(),
                            'hours' => $vOrg->numUnapprovedHours(),
                            'places' => $vOrg->numUnapprovedPlaces(), ], ];
                },
                'transformOutputJson', ]);

        $route->execute($req, $res, $app);
    }

    public function apiDashboard($req, $res)
    {
        $app = $this->app;

        $route = new ApiRoute();
        $route->addQueryParams([
                    'model' => 'app\organizations\models\Organization',
                    'model_id' => $req->params('id'), ])
              ->addParseSteps([
                'parseRequireJson',
                'parseModelFindOneParameters', ])
              ->addQueryStep('queryModelFindOne')
              ->addTransformSteps([
                function (&$result, $route) use ($app) {
                    $vOrg = $result->volunteerOrganization();

                    if (!$vOrg) {
                        $route->getResponse()->setCode(404);

                        return false;
                    }

                    if (!$vOrg->can('admin', $app['user'])) {
                        $route->getResponse()->setCode(403);

                        return false;
                    }

                    $start = strtotime('-11 months');
                    $end = time();

                    $result = [
                        'dashboard' => [
                            'totals' => [
                                'volunteers' => $vOrg->numVolunteers(),
                                'hours' => $vOrg->totalHoursVolunteered(),
                                'places' => $vOrg->numPlaces(), ],
                            'alerts' => $vOrg->alerts(),
                            'period' => $vOrg->activityForPeriod($start, $end), ], ];
                },
                'transformOutputJson', ]);

        $route->execute($req, $res, $app);
    }

    public function apiVolunteers($req, $res)
    {
        $route = new ApiRoute();
        $route->addQueryParams([
                'module' => 'volunteers',
                'model' => 'app\volunteers\models\Volunteer', ])
              ->addParseSteps([
                'parseRequireJson',
                'parseRequireFindPermission',
                'parseModelFindAllParameters',
                function ($route) {
                    // determine status
                    $status = $route->getRequest()->query('status');
                    $where = $route->getQueryParams('where');
                    if ($status == 'awaiting_approval') {
                        $where['role'] = Volunteer::ROLE_AWAITING_APPROVAL;
                    } elseif ($status == 'volunteer') {
                        $where['role'] = Volunteer::ROLE_VOLUNTEER;
                    } elseif ($status == 'missing_application') {
                        // TODO
                    } elseif ($status == 'not_registered') {
                        // TODO
                    } elseif ($status == 'volunteer_coordinator') {
                        $where['role'] = Volunteer::ROLE_ADMIN;
                    }
                    $route->addQueryParams(['where' => $where]);
                }, ])
              ->addQueryStep('queryModelFindAll')
              ->addTransformSteps([
                'transformModelFindAll',
                'transformPaginate',
                'transformOutputJson', ]);

        $route->execute($req, $res, $this->app);
    }

    public function apiInviteVolunteers($req, $res)
    {
        $emails = $req->request('emails');
        $n = 0;
        foreach ($emails as $email) {
            if ($org->inviteVolunteer($email)) {
                ++$n;
            } else {
                $this->app['errors']->push([
                    'error' => 'could_not_add_volunteer_email',
                    'params' => [
                        'email' => $email, ], ]);
            }
        }

        $req->setParams(['numAdded' => $n]);
        $success = count($emails) == $n;
    }

    public function apiVolunteerActivity($req, $res)
    {
        $route = new ApiRoute();
        $route->addQueryParams([
                'module' => 'volunteers',
                'model' => 'app\volunteers\models\Volunteer',
                'organization' => $req->query('organization'),
                'start' => $req->query('start'),
                'end' => $req->query('end'), ])
              ->addParseSteps([
                'parseRequireJson',
                'parseModelFindOneParameters', ])
              ->addQueryStep('queryModelFindOne')
              ->addTransformSteps([
                'transformModelFindOne',
                function (&$result, $route) {
                    $volunteer = $result;
                    $modelClass = $route->getQueryParams('model');

                    if ($volunteer instanceof $modelClass) {
                        $org = new Organization($route->getQueryParams('organization'));
                        if (!$org->hasVolunteerOrganization()) {
                            return $route->getResponse()->setCode(404);
                        }
                        $vOrg = $org->volunteerOrganization();

                        // look up volunteer hours using the organization
                        $hIter = $vOrg->hours($route->getQueryParams('start'), $route->getQueryParams('end'), $volunteer);
                        $hours = [];
                        foreach ($hIter as $hour) {
                            $hours[] = $hour->toArray([], [], ['place']);
                        }

                        $result = [
                            'volunteer_hours' => $hours, ];
                    }
                },
                'transformOutputJson', ]);

        $route->execute($req, $res, $this->app);
    }

    public function apiVolunteerHours($req, $res)
    {
        $route = new ApiRoute();
        $route->addQueryParams([
                'module' => 'volunteers',
                'model' => 'app\volunteers\models\VolunteerHour', ])
              ->addParseSteps([
                'parseRequireJson',
                'parseRequireFindPermission',
                'parseModelFindAllParameters',
                function ($route) {
                    // determine status
                    $status = $route->getRequest()->query('status');
                    $where = $route->getQueryParams('where');
                    if ($status == 'approved') {
                        $where['approved'] = true;
                    } elseif ($status == 'verification_requested') {
                        $where['approved'] = false;
                        $where['verification_requested'] = true;
                    } elseif ($status == 'unapproved') {
                        $where['approved'] = false;
                    }
                    $route->addQueryParams(['where' => $where]);
                }, ])
              ->addQueryStep('queryModelFindAll')
              ->addTransformSteps([
                'transformModelFindAll',
                'transformPaginate',
                'transformOutputJson', ]);

        $route->execute($req, $res, $this->app);
    }

    public function apiVolunteerPlaces($req, $res)
    {
        $route = new ApiRoute();
        $route->addQueryParams([
                'module' => 'volunteers',
                'model' => 'app\volunteers\models\VolunteerPlace', ])
              ->addParseSteps([
                'parseRequireJson',
                'parseRequireFindPermission',
                'parseModelFindAllParameters',
                function ($route) {
                    // determine type
                    $type = $route->getRequest()->query('type');
                    $where = $route->getQueryParams('where');
                    if ($type == 'internal') {
                        $where['place_type'] = VolunteerPlace::INTERNAL;
                    } elseif ($type == 'external') {
                        $where['place_type'] = VolunteerPlace::EXTERNAL;
                    } elseif ($type == 'unapproved') {
                        $where['place_type'] = VolunteerPlace::EXTERNAL;
                        $where['verify_approved'] = false;
                    }
                    $route->addQueryParams(['where' => $where]);
                }, ])
              ->addQueryStep('queryModelFindAll')
              ->addTransformSteps([
                'transformModelFindAll',
                'transformPaginate',
                'transformOutputJson', ]);

        $route->execute($req, $res, $this->app);
    }
}
