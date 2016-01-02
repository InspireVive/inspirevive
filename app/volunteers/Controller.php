<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\volunteers;

use infuse\Database;
use infuse\Locale;
use infuse\Request;
use infuse\Utility as U;
use infuse\Validate;
use infuse\View;
use app\organizations\models\Organization;
use app\reports\libs\Report;
use app\volunteers\models\VolunteerOrganization;
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerApplication;
use app\volunteers\models\VolunteerHour;
use app\volunteers\models\VolunteerPlace;

class Controller
{
    use \InjectApp;

    public static $properties = [
        'models' => [
            'VolunteerOrganization',
            'Volunteer',
            'VolunteerApplication',
            'VolunteerHour',
            'VolunteerHourTag',
            'VolunteerPlace',
       ],
        'defaultModel' => 'Volunteer',
        'routes' => [],
   ];

    public static $scaffoldAdmin;

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
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', '/volunteers/application', time() + 3600, '/');

            $res->redirect('/signup');

            return false;
        }

        $applicationObj = $currentUser->volunteerApplication();

        $application = $req->request();

        if (empty($application['first_name']) && $applicationObj->exists()) {
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
       ]);
    }

    public function submitVolunteerApplication($req, $res)
    {
        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', '/volunteers/application', time() + 3600, '/');

            return $res->redirect('/login');
        }

        if (!$req->request('accept')) {
            $req->setParams(['accept_error' => true]);

            return $this->volunteerApplication($req, $res);
        }

        $input = $req->request();

        $input['uid'] = $currentUser->id();
        $input['birth_date'] = mktime(0, 0, 0, $input['month'] + 1, $input['day'], $input['year']);
        $input['first_time_volunteer'] = !U::array_value($input, 'volunteered_before');

        $application = $currentUser->volunteerApplication();

        if (!$application->exists()) {
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
        $req->setParams([
            'type' => 'organizations', ]);

        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

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
                'start' => false,],];

        foreach ($periods as $k => $period) {
            $periods[$k]['hoursVolunteered'] = $volunteerOrg->totalHoursVolunteered($period['start']);
            $periods[$k]['volunteers'] = $volunteerOrg->numVolunteers($period['start']);
            $topVolunteers = $volunteerOrg->topVolunteers(1, $period['start']);
            $periods[$k]['topVolunteer'] = (count($topVolunteers) == 1) ? $topVolunteers[0] : false;
        }

        $role = $org->getRoleOfUser($this->app['user']);

        return new View('profile', [
            'title' => $volunteerOrg->name().' Volunteer Hub',
            'org' => $volunteerOrg->toArray(),
            'orgObj' => $volunteerOrg,
            'slug' => $org->slug,
            'awaitingApproval' => $role == Volunteer::ROLE_AWAITING_APPROVAL,
            'isVolunteer' => $role >= Volunteer::ROLE_VOLUNTEER,
            'isVolunteerCoordinator' => $role == Volunteer::ROLE_ADMIN,
            'topVolunteers' => $volunteerOrg->topVolunteers(6),
            'periods' => $periods,
            'rsvpd' => $req->query('rsvpd'),
       ]);
    }

    public function joinOrganization($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', $volunteerOrg->url(), time() + 3600, '/');

            return $res->redirect('/signup');
        }

        // application shared by default unless explicitly specified
        $applicationShared = $req->request('application_shared') === null || $req->request('application_shared');

        $volunteer = new Volunteer([$currentUser->id(), $org->id()]);

        $uid = $currentUser->id();

        // make sure the application has been shared with the org
        if ($volunteer->exists()) {
            $role = max($volunteer->role, Volunteer::ROLE_VOLUNTEER);
            $volunteer->grantAllPermissions();
            $volunteer->set([
                'application_shared' => $applicationShared,
                'role' => $role, ]);
        }
        // create a new volunteer
        else {
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
            'title' => 'Joined '.$volunteerOrg->name(),
            'org' => $volunteerOrg->toArray(),
            'orgObj' => $volunteerOrg,
       ]);
    }

    public function approveVolunteer($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $volunteer = Volunteer::findOne([
            'where' => [
                'organization' => $org->id(),
                'approval_link' => $req->params('approval_link'),
                'role' => Volunteer::ROLE_AWAITING_APPROVAL, ], ]);

        if (!$volunteer) {
            return $res->setCode(404);
        }

        $volunteer->grantAllPermissions();
        $success = $volunteer->set('role', Volunteer::ROLE_VOLUNTEER);

        $user = $volunteer->relation('uid');

        return new View('volunteerApprovedThanks', [
            'org' => $volunteerOrg,
            'title' => ($success) ? 'Volunteer Approved' : 'Could not approve volunteer',
            'success' => $success,
            'volunteer' => $volunteer->toArray(),
            'name' => $user->name(true),
            'user' => $user->toArray(),
            'approved' => true, ]);
    }

    public function rejectVolunteer($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $volunteer = Volunteer::findOne([
            'where' => [
                'organization' => $org->id(),
                'approval_link' => $req->params('approval_link'),
                'role' => Volunteer::ROLE_AWAITING_APPROVAL, ], ]);

        if (!$volunteer) {
            return $res->setCode(404);
        }

        $volunteer->grantAllPermissions();
        $success = $volunteer->delete();

        return new View('volunteerApprovedThanks', [
            'org' => $volunteerOrg,
            'title' => ($success) ? 'Volunteer Denied' : 'Could not deny volunteer',
            'success' => $success,
            'volunteer' => $volunteer->toArray(),
            'name' => $volunteer->relation('uid')->name(true),
            'approved' => false, ]);
    }

    public function unjoinOrganization($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            return $res->redirect('/login');
        }

        $volunteer = new Volunteer([$currentUser->id(), $org->id()]);

        $volunteer->grantAllPermissions();
        $volunteer->set([
            'role' => Volunteer::ROLE_NONE, ]);

        if ($req->query('redir') == 'profile') {
            return $res->redirect('/profile');
        }
    }

    public function reportHoursStep1($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', $volunteerOrg->url().'/hours/report', time() + 3600, '/');

            return $res->redirect('/login');
        }

        $places = VolunteerPlace::findAll([
            'where' => [
                'organization' => $org->id(), ],
            'sort' => 'name ASC', ]);

        return new View('reportHours', [
            'org' => $volunteerOrg,
            'title' => 'Report Volunteer Hours',
            'places' => $places, ]);
    }

    public function addVolunteerPlaceForm($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', $volunteerOrg->url().'/places/add', time() + 3600, '/');

            return $res->redirect('/login');
        }

        return new View('addPlace', [
            'org' => $volunteerOrg,
            'title' => 'Add Volunteer Place',
            'place' => $req->request(), ]);
    }

    public function addVolunteerPlace($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', $volunteerOrg->url().'/places/add', time() + 3600, '/');

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
        $input['place_type'] = VOLUNTEER_PLACE_EXTERNAL;
        $input['verify_approved'] = false;

        $place = new VolunteerPlace();
        $success = $place->create($input);

        if ($success) {
            $res->redirect($volunteerOrg->url().'/hours/report/2?place='.$place->id());
        } else {
            return $this->addVolunteerPlaceForm($req, $res);
        }
    }

    public function reportHoursStep2($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', $volunteerOrg->url().'/hours/report', time() + 3600, '/');

            return $res->redirect('/login');
        }

        $place = $req->query('place');
        if ($place) {
            if ($place == -1) {
                return $res->redirect($volunteerOrg->url().'/places/add');
            }

            $place = new VolunteerPlace($place);
        }

        if (!$place || !$place->exists()) {
            return $res->redirect($volunteerOrg->url().'/hours/add');
        }

        $dayTs = $req->request('timestamp');

        $availableTags = (array) Database::select(
            'VolunteerHourTags',
            'tag',
            [
                'where' => [
                    'organization' => $org->id(), ],
                'fetchStyle' => 'singleColumn',
                'orderBy' => 'RAND()',
                'groupBy' => 'tag',
                'limit' => 10, ]);

        return new View('reportHours2', [
            'org' => $volunteerOrg,
            'title' => 'Report Volunteer Hours',
            'place' => $place,
            'tags' => $req->request('tags'),
            'timestamp' => $dayTs,
            'hours' => $req->request('hours'),
            'availableTags' => $availableTags, ]);
    }

    public function reportHoursStep3($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            setcookie('redirect', $volunteerOrg->url().'/hours/report', time() + 3600, '/');

            return $res->redirect('/login');
        }

        $place = $req->query('place');
        if ($place) {
            $place = new VolunteerPlace($place);
        }

        if (!$place || !$place->exists()) {
            return $res->redirect($volunteerOrg->manageUrl().'/hours/add');
        }

        // validate tags
        $tags = $req->request('tags');
        foreach ((array) explode(' ', $tags) as $tag) {
            if (!Validate::is($tag, 'alpha_dash')) {
                $this->app['errors']->push(['error' => 'invalid_volunteer_hour_tags']);

                return $this->reportHoursStep2($req, $res);
            }
        }

        // validate day
        $date = \DateTime::createFromFormat('M j, Y', $req->request('timestamp'));
        if (!$date) {
            $this->app['errors']->push([
                'error' => 'validation_failed',
                'params' => [
                    'field_name' => 'Day', ], ]);

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
            $res->redirect($volunteerOrg->url().'/hours/thanks');
        } else {
            return $this->reportHoursStep2($req, $res);
        }
    }

    public function reportHoursThanks($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $currentUser = $this->app['user'];

        // make sure the user is logged in
        if (!$currentUser->isLoggedIn()) {
            return $res->redirect('/login');
        }

        return new View('reportHoursThanks', [
            'org' => $volunteerOrg,
            'title' => 'Volunteer Hours Reported', ]);
    }

    public function approveHours($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $hour = VolunteerHour::findOne([
            'where' => [
                'organization' => $org->id(),
                'approval_link' => $req->params('approval_link'), ], ]);

        if (!$hour) {
            return new View('hoursNotFound', [
                'org' => $volunteerOrg,
                'title' => 'Hours Not Found', ]);
        }

        $hour->grantAllPermissions();
        $success = $hour->set('approved', true);

        $h = $hour->toArray();
        $user = $hour->relation('uid');
        $place = $hour->place()->toArray();

        return new View('hoursApprovedThanks', [
            'org' => $volunteerOrg,
            'title' => ($success) ? 'Volunteer Hours Approved' : 'Could not approve volunteer hours',
            'success' => $success,
            'hour' => $h,
            'user' => $user,
            'place' => $place,
            'approved' => true, ]);
    }

    public function rejectHours($req, $res)
    {
        $volunteerOrg = $this->getVolunteerOrg($req, $res);

        if (!is_object($volunteerOrg)) {
            return $volunteerOrg;
        }

        $org = $volunteerOrg->relation('organization');

        $hour = VolunteerHour::findOne([
            'where' => [
                'organization' => $org->id(),
                'approval_link' => $req->params('approval_link'), ], ]);

        if (!$hour) {
            return new View('hoursNotFound', [
                'org' => $volunteerOrg,
                'title' => 'Hours Not Found', ]);
        }

        $h = $hour->toArray();
        $user = $hour->relation('uid');
        $place = $hour->place()->toArray();

        $hour->grantAllPermissions();
        $success = $hour->delete();

        return new View('hoursApprovedThanks', [
            'org' => $volunteerOrg,
            'title' => ($success) ? 'Volunteer Hours Denied' : 'Could not reject volunteer hours',
            'success' => $success,
            'hour' => $h,
            'user' => $user,
            'place' => $place,
            'approved' => false, ]);
    }

/*
--- Background Tasks ---
*/

    public function cron($command)
    {
        if ($command == 'unapproved-hour-notifications') {
            return VolunteerOrganization::processUnapprovedNotifications();
        }
    }

/*
--- Helper Functions ---
*/

    private function getOrg($req, $res)
    {
        $org = Organization::findOne([
            'where' => [
                'slug' => $req->params('slug'), ], ]);

        if (!$org) {
            $res->setCode(404);

            return false;
        }

        return $org;
    }

    private function getVolunteerOrg($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $volunteerOrg = $org->volunteerOrganization();

        if (!$volunteerOrg) {
            $res->setCode(404);

            return false;
        }

        return $volunteerOrg;
    }
}
