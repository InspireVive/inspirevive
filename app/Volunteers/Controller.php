<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Volunteers;

use App\Organizations\Models\Organization;
use App\Volunteers\Models\Volunteer;
use App\Volunteers\Models\VolunteerApplication;
use App\Volunteers\Models\VolunteerHour;
use App\Volunteers\Models\VolunteerPlace;
use Infuse\HasApp;
use Infuse\Locale;
use Infuse\Utility as U;
use Infuse\View;

class Controller
{
    use HasApp;

    public static $viewsDir;

    private $months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',];

    public function __construct()
    {
        self::$viewsDir = __DIR__.'/views';
    }

    public function volunteerApplication($req, $res)
    {
        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $req->setCookie('redirect', '/volunteers/application', time() + 3600, '/');

            $res->redirect('/signup');

            return false;
        }

        $applicationObj = $currentUser->volunteerApplication();

        $application = $req->request();

        if (empty($application['first_name']) && $applicationObj) {
            $application = $applicationObj->toArray();
            $application['day'] = date('d', $application['birth_date']);
            $application['month'] = date('n', $application['birth_date']) - 1;
            $application['year'] = date('Y', $application['birth_date']);
        }

        return new View('application', [
            'title' => 'Volunteer Application',
            'application' => $application,
            'states' => Locale::$usaStates,
            'days' => range(1, 31),
            'months' => $this->months,
            'years' => range(date('Y'), 1900),
            'accept_error' => $req->params('accept_error'),
            'req' => $req
       ]);
    }

    public function submitVolunteerApplication($req, $res)
    {
        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $req->setCookie('redirect', '/volunteers/application', time() + 3600, '/');

            return $res->redirect('/login');
        }

        if (!$req->request('accept')) {
            $req->setParams(['accept_error' => true]);

            return $this->volunteerApplication($req, $res);
        }

        $input = $req->request();

        $input['uid'] = $currentUser->id();
        $input['birth_date'] = mktime(0, 0, 0, $input['month'] + 1, $input['day'], $input['year']);
        $input['first_time_volunteer'] = !array_value($input, 'volunteered_before');

        $application = $currentUser->volunteerApplication();

        if (!$application) {
            $application = new VolunteerApplication();
            if ($application->create($input)) {
                return $res->redirect('/volunteers/application/thanks');
            }
        } else {
            if ($application->set($input)) {
                return $res->redirect('/volunteers/application/thanks');
            }
        }

        return $this->volunteerApplication($req, $res);
    }

    public function volunteerApplicationThanks($req, $res)
    {
        return new View('applicationThanks', [
            'title' => 'Thank you for completing the volunteer application',
       ]);
    }

/*
--- Volunteer Hub ---
*/

    public function volunteerHub($req, $res)
    {
        $req->setParams(['type' => 'organizations']);

        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $periods = [
            [
                'title' => 'This Week',
                'start' => VolunteerHour::timestampToStartOfDay(strtotime('last Sunday')),],
            [
                'title' => 'This Month',
                'start' => VolunteerHour::timestampToStartOfDay(mktime(0, 0, 0, date('m'), 1, date('y'))),],
            [
                'title' => 'This Year',
                'start' => VolunteerHour::timestampToStartOfDay(mktime(0, 0, 0, 1, 1, date('y'))),],
            [
                'title' => 'All Time',
                'start' => false,
            ],
        ];

        foreach ($periods as $k => $period) {
            $periods[$k]['hoursVolunteered'] = $org->totalHoursVolunteered($period['start']);
            $periods[$k]['volunteers'] = $org->numVolunteers($period['start']);
            $topVolunteers = $org->topVolunteers(1, $period['start']);
            $periods[$k]['topVolunteer'] = (count($topVolunteers) == 1) ? $topVolunteers[0] : false;
        }

        $role = $org->getRoleOfUser($this->app['user']);

        return new View('profile', [
            'title' => $org->name.' Volunteer Hub',
            'org' => $org->toArray(),
            'orgObj' => $org,
            'username' => $org->username,
            'awaitingApproval' => $role == Volunteer::ROLE_AWAITING_APPROVAL,
            'isVolunteer' => $role >= Volunteer::ROLE_VOLUNTEER,
            'isVolunteerCoordinator' => $role == Volunteer::ROLE_ADMIN,
            'topVolunteers' => $org->topVolunteers(6),
            'periods' => $periods,
            'rsvpd' => $req->query('rsvpd'),
       ]);
    }

    public function joinOrganization($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $req->setCookie('redirect', $org->url(), time() + 3600, '/');

            return $res->redirect('/signup');
        }

        // application shared by default unless explicitly specified
        $applicationShared = $req->request('application_shared') === null || $req->request('application_shared');

        $volunteer = Volunteer::find([$currentUser->id(), $org->id()]);

        $uid = $currentUser->id();

        // make sure the application has been shared with the org
        if ($volunteer) {
            $role = max($volunteer->role, Volunteer::ROLE_VOLUNTEER);
            $volunteer->grantAllPermissions();
            $volunteer->application_shared = $applicationShared;
            $volunteer->role = $role;
            $volunteer->saveOrFail();
        } else {
            // create a new volunteer
            $volunteer = new Volunteer();
            $volunteer->create([
                'uid' => $uid,
                'organization' => $org->id(),
                'application_shared' => $applicationShared,
                'active' => true,
                'role' => Volunteer::ROLE_AWAITING_APPROVAL, ]);
        }

        if ($req->query('redir') == 'profile') {
            return $res->redirect('/profile');
        }

        return new View('joinOrganization', [
            'title' => 'Joined '.$org->name,
            'org' => $org->toArray(),
            'orgObj' => $org,
       ]);
    }

    public function approveVolunteer($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $volunteer = Volunteer::where('organization', $org->id())
            ->where('approval_link', $req->params('approval_link'))
            ->where('role', Volunteer::ROLE_AWAITING_APPROVAL)
            ->first();

        if (!$volunteer) {
            return $res->setCode(404);
        }

        $volunteer->grantAllPermissions();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $success = $volunteer->save();

        $user = $volunteer->relation('uid');

        return new View('volunteerApprovedThanks', [
            'org' => $org,
            'title' => ($success) ? 'Volunteer Approved' : 'Could not approve volunteer',
            'success' => $success,
            'volunteer' => $volunteer->toArray(),
            'name' => $user->name(true),
            'user' => $user->toArray(),
            'approved' => true, ]);
    }

    public function rejectVolunteer($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $volunteer = Volunteer::where('organization', $org->id())
            ->where('approval_link', $req->params('approval_link'))
            ->where('role', Volunteer::ROLE_AWAITING_APPROVAL)
            ->first();

        if (!$volunteer) {
            return $res->setCode(404);
        }

        $volunteer->grantAllPermissions();
        $success = $volunteer->delete();

        return new View('volunteerApprovedThanks', [
            'org' => $org,
            'title' => ($success) ? 'Volunteer Denied' : 'Could not deny volunteer',
            'success' => $success,
            'volunteer' => $volunteer->toArray(),
            'name' => $volunteer->relation('uid')->name(true),
            'approved' => false, ]);
    }

    public function unjoinOrganization($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            return $res->redirect('/login');
        }

        $volunteer = new Volunteer([$currentUser->id(), $org->id()]);

        $volunteer->grantAllPermissions();
        $volunteer->role = Volunteer::ROLE_NONE;
        $volunteer->saveOrFail();

        if ($req->query('redir') == 'profile') {
            return $res->redirect('/profile');
        }
    }

    public function reportHoursStep1($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $req->setCookie('redirect', $org->url().'/hours/report', time() + 3600, '/');

            return $res->redirect('/login');
        }

        $places = VolunteerPlace::where('organization', $org->id())
            ->sort('name ASC')
            ->all();

        return new View('reportHours', [
            'org' => $org,
            'title' => 'Report Volunteer Hours',
            'places' => $places,
            'req' => $req
        ]);
    }

    public function addVolunteerPlaceForm($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $req->setCookie('redirect', $org->url().'/places/add', time() + 3600, '/');

            return $res->redirect('/login');
        }

        return new View('addPlace', [
            'org' => $org,
            'title' => 'Add Volunteer Place',
            'place' => $req->request(),
            'req' => $req
        ]);
    }

    public function addVolunteerPlace($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $req->setCookie('redirect', $org->url().'/places/add', time() + 3600, '/');

            return $res->redirect('/login');
        }

        // enforce all fields of input
        VolunteerPlace::$properties['address']['required'] = true;
        VolunteerPlace::$properties['address']['validate'] = 'string:5';
        VolunteerPlace::$properties['verify_name']['required'] = true;
        VolunteerPlace::$properties['verify_name']['validate'] = 'string:5';
        VolunteerPlace::$properties['verify_email']['required'] = true;
        VolunteerPlace::$properties['verify_email']['validate'] = 'email';

        $input = $req->request();
        $input['organization'] = $org->id();
        $input['place_type'] = VolunteerPlace::EXTERNAL;
        $input['verify_approved'] = false;

        $place = new VolunteerPlace();
        $success = $place->create($input);

        if ($success) {
            $res->redirect($org->url().'/hours/report/2?place='.$place->id());
        } else {
            return $this->addVolunteerPlaceForm($req, $res);
        }
    }

    public function reportHoursStep2($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $res->setCookie('redirect', $org->url().'/hours/report', time() + 3600, '/');

            return $res->redirect('/login');
        }

        // find the place
        $placeId = $req->query('place');
        if ($placeId == -1) {
            return $res->redirect($org->url().'/places/add');
        }

        $place = VolunteerPlace::find($placeId);
        if (!$place) {
            return $res->redirect($org->url().'/hours/add');
        }

        $dayTs = $req->request('timestamp');

        $db = $this->getApp()['database']->getDefault();
        $availableTags = (array) $db->select('tag')
            ->from('VolunteerHourTags')
            ->where('organization', $org->id())
            ->orderBy('RAND()')
            ->groupBy('tag')
            ->limit(10)
            ->column();

        return new View('reportHours2', [
            'org' => $org,
            'title' => 'Report Volunteer Hours',
            'place' => $place,
            'tags' => $req->request('tags'),
            'timestamp' => $dayTs,
            'hours' => $req->request('hours'),
            'availableTags' => $availableTags,
            'req' => $req
        ]);
    }

    public function reportHoursStep3($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            $res->setCookie('redirect', $org->url().'/hours/report', time() + 3600, '/');

            return $res->redirect('/login');
        }

        // find the place
        $place = VolunteerPlace::find($req->query('place'));
        if (!$place) {
            return $res->redirect($org->url().'/hours/add');
        }

        // validate tags
        $tags = $req->request('tags');
        foreach ((array) explode(' ', $tags) as $tag) {
            if (!preg_match('/^[A-Za-z0-9_-]*$/', $tag)) {
                $this->app['errors']->add('invalid_volunteer_hour_tags');

                return $this->reportHoursStep2($req, $res);
            }
        }

        // validate day
        $date = \DateTime::createFromFormat('M j, Y', $req->request('timestamp'));
        if (!$date) {
            $this->app['errors']->add('validation_failed', ['field_name' => 'Day']);

            return $this->reportHoursStep2($req, $res);
        }

        $uid = $currentUser->id();

        $hour = new VolunteerHour();
        $success = $hour->create([
            'organization' => $org->id(),
            'uid' => $uid,
            'hours' => $req->request('hours'),
            'timestamp' => $date->getTimestamp(),
            'place' => $place->id(),
            'approved' => false,
            'tags' => $tags, ]);

        if ($success) {
            $res->redirect($org->url().'/hours/thanks');
        } else {
            return $this->reportHoursStep2($req, $res);
        }
    }

    public function reportHoursThanks($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isSignedIn()) {
            return $res->redirect('/login');
        }

        return new View('reportHoursThanks', [
            'org' => $org,
            'title' => 'Volunteer Hours Reported',
        ]);
    }

    public function approveHours($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $hour = VolunteerHour::where('organization', $org->id())
            ->where('approval_link', $req->params('approval_link'))
            ->first();

        if (!$hour) {
            return new View('hoursNotFound', [
                'org' => $org,
                'title' => 'Hours Not Found',
            ]);
        }

        $hour->grantAllPermissions();
        $hour->approved = true;
        $success = $hour->save();

        $h = $hour->toArray();
        $user = $hour->relation('uid');
        $place = $hour->place()->toArray();

        return new View('hoursApprovedThanks', [
            'org' => $org,
            'title' => ($success) ? 'Volunteer Hours Approved' : 'Could not approve volunteer hours',
            'success' => $success,
            'hour' => $h,
            'user' => $user,
            'place' => $place,
            'approved' => true,
        ]);
    }

    public function rejectHours($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $hour = VolunteerHour::where('organization', $org->id())
            ->where('approval_link', $req->params('approval_link'))
            ->first();

        if (!$hour) {
            return new View('hoursNotFound', [
                'org' => $org,
                'title' => 'Hours Not Found',
            ]);
        }

        $h = $hour->toArray();
        $user = $hour->relation('uid');
        $place = $hour->place()->toArray();

        $hour->grantAllPermissions();
        $success = $hour->delete();

        return new View('hoursApprovedThanks', [
            'org' => $org,
            'title' => ($success) ? 'Volunteer Hours Denied' : 'Could not reject volunteer hours',
            'success' => $success,
            'hour' => $h,
            'user' => $user,
            'place' => $place,
            'approved' => false,
        ]);
    }

/*
--- Helper Functions ---
*/

    private function getOrg($req, $res)
    {
        $org = Organization::where('username', $req->params('username'))
            ->first();

        if (!$org) {
            $res->setCode(404);

            return false;
        }

        return $org;
    }
}
