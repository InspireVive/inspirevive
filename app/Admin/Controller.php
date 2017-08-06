<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Admin;

use App\Organizations\Models\Organization;
use App\Reports\Libs\Report;
use App\Users\Models\User;
use App\Volunteers\Models\Volunteer;
use App\Volunteers\Models\VolunteerHour;
use App\Volunteers\Models\VolunteerPlace;
use Infuse\HasApp;
use Infuse\Utility as U;
use Infuse\View;

class Controller
{
    use HasApp;

    public static $viewsDir;

    static $sectionModels = [
        'volunteers' => 'App\Volunteers\Models\Volunteer',
        'hours' => 'App\Volunteers\Models\VolunteerHour',
        'places' => 'App\Volunteers\Models\VolunteerPlace',
    ];

    public function __construct()
    {
        self::$viewsDir = __DIR__.'/views';
    }

/*
--- Admin: Dashboard ---
*/

    public function index($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $periods = [
            [
                'title' => 'This Week',
                'start' => VolunteerHour::timestampToStartOfDay(strtotime('last Sunday')), ],
            [
                'title' => 'This Month',
                'start' => VolunteerHour::timestampToStartOfDay(mktime(0, 0, 0, date('m'), 1, date('y'))), ],
            [
                'title' => 'This Year',
                'start' => VolunteerHour::timestampToStartOfDay(mktime(0, 0, 0, 1, 1, date('y'))), ],
            [
                'title' => 'All Time',
                'start' => false, ], ];

        foreach ($periods as $k => $period) {
            $periods[$k]['hoursVolunteered'] = $org->totalHoursVolunteered($period['start']);
            $periods[$k]['volunteers'] = $org->numVolunteers($period['start']);
            $topVolunteers = $org->topVolunteers(1, $period['start']);
            $periods[$k]['topVolunteer'] = (count($topVolunteers) == 1) ? $topVolunteers[0] : false;
        }

        return new View('dashboard', [
            'org' => $org,
            'title' => 'Pulse',
            'periods' => $periods,
            'dashboardPage' => true,
        ]);
    }

/*
--- Admin: Volunteers ---
*/

    public function volunteersBrowse($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $limit = 100;
        $page = max(0, $req->query('page'));
        $showInactive = !!$req->query('inactive');
        $showApproved = $req->query('approved');
        if ($showApproved === null) {
            $showApproved = true;
        }

        $roleSql = $showApproved ?
            'role >= '.Volunteer::ROLE_VOLUNTEER :
            'role = '.Volunteer::ROLE_AWAITING_APPROVAL;

        $volunteers = Volunteer::where('organization', $org->id())
            ->where($roleSql)
            ->where('active', !$showInactive)
            ->where('uid IS NOT NULL')
            ->sort('uid ASC')
            ->start($page * $limit)
            ->first($limit);

        $count = count($volunteers);

        if ($req->query('error') == 'cannot_delete_self') {
            $this->app['errors']->add('As a volunteer coordinator, you cannot remove yourself.');
        }

        return new View('volunteers/browse', [
            'org' => $org,
            'title' => 'Volunteers',
            'volunteersPage' => true,
            'volunteers' => $volunteers,
            'showApproved' => $showApproved,
            'showInactive' => $showInactive,
            'hasLess' => $page > 0,
            'hasMore' => $count > $limit * ($page + 1),
            'page' => $page,
            'count' => $count,
            'numAdded' => $req->params('numAdded'),
            'usernameNotFound' => $req->params('usernameNotFound'),
            'username' => $req->query('username')
        ]);
    }

    function volunteersLookupByUsername($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $username = $req->query('username');

        $user = User::where('username', $username)->first();

        if ($user && $org->getRoleOfUser($user) >= Volunteer::ROLE_AWAITING_APPROVAL) {
            return $res->redirect($org->manageUrl().'/volunteers/'.$user->id());
        }

        $req->setParams(['usernameNotFound' => true]);

        return $this->volunteersBrowse($req, $res);
    }

    public function addVolunteerForm($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        return new View('volunteers/add', [
            'org' => $org,
            'title' => 'Add Volunteers',
            'volunteersPage' => true,
            'emails' => $req->request('emails'),
            'numAdded' => $req->params('numAdded'), ]);
    }

    public function addVolunteer($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return $org;
        }

        $success = false;

        if ($req->request('emails')) {
            $emails = explode("\n", $req->request('emails'));
            $n = 0;
            foreach ($emails as $email) {
                if ($org->inviteVolunteer($email)) {
                    ++$n;
                } else {
                    $params = ['email' => $email];
                    $this->app['errors']->add('could_not_add_volunteer_email', $params);
                }
            }

            $req->setParams(['numAdded' => $n]);
            $success = count($emails) == $n;

            if (!$success) {
                return $this->addVolunteerForm($req, $res);
            }
        } elseif ($req->files('import')) {
            $file = $req->files('import');
            $n = 0;

            // check the upload is valid
            if (!is_array($file) || !array_value($file, 'error') === 0 || !array_value($file, 'size') > 0 || !array_value($file, 'tmp_name')) {
                $this->app['errors']->add('There was a problem with the upload.');
            } else {
                $expFilename = explode('.', array_value($file, 'name'));
                $ext = strtolower(end($expFilename));
                $new = U::guid().'.'.$ext;
                $temp = INFUSE_BASE_DIR.'/temp/uploads/'.$new;

                // check extension
                if ($ext != 'csv') {
                    $this->app['errors']->add('The file type is invalid. Only .csv files are allowed.');
                } else {
                    // move uploaded file to temp dir
                    if (!move_uploaded_file($file['tmp_name'], $temp)) {
                        $this->app['errors']->add('There was an error processing your upload.');
                    } else {
                        // bugfix for csvs created on macs
                        ini_set('auto_detect_line_endings', true);

                        $columnMapping = [];

                        $handle = fopen($temp, 'r');

                        if ($handle !== false) {
                            $isFirst = true;

                            while (($line = fgetcsv($handle, 1000, ',')) !== false) {
                                if ($isFirst) {
                                    // determine column mapping
                                    foreach ($line as $field) {
                                        $columnMapping[] = $field;
                                    }

                                    $isFirst = false;
                                    continue;
                                }

                                // map csv columns to fields
                                $fields = [];
                                foreach ($columnMapping as $k => $field) {
                                    $fields[$field] = $line[$k];
                                }

                                if (!isset($fields['email'])) {
                                    continue;
                                }

                                $volunteer = $org->inviteVolunteer($fields['email']);

                                if ($volunteer) {
                                    unset($fields['email']);

                                    // add any meta-data to volunteer
                                    if (count($fields) > 0) {
                                        $volunteer->metadata = json_encode($fields);
                                        $volunteer->save();
                                    }

                                    ++$n;
                                }
                            }

                            fclose($handle);
                        }

                        // delete the temp file
                        @unlink($temp);

                        $success = true;

                        $req->setParams(['numAdded' => $n]);
                    }
                }
            }

            if (!$success) {
                return $this->addVolunteerImportForm($req, $res);
            }
        }

        if ($success) {
            return $this->volunteersBrowse($req, $res);
        }
    }

    public function addVolunteerImportForm($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        return new View('volunteers/addImport', [
            'org' => $org,
            'title' => 'Import Volunteers',
            'volunteersPage' => true,
            'error' => $req->params('error'),
            'success' => $req->params('success'),
            'numAdded' => $req->params('numAdded'), ]);
    }

    public function volunteersView($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $volunteer, $section) = $lookup;

        $user = $volunteer->relation('uid');
        $completed = $user->hasCompletedVolunteerApplication();
        $application = ($completed && $volunteer->application_shared) ? $user->volunteerApplication() : false;

        $hours = $org->hours(false, false, $volunteer);

        $name = $user->name(true);

        return new View('volunteers/view', [
            'org' => $org,
            'volunteer' => $volunteer->toArray(),
            'user' => $user->toArray(),
            'application' => ($application) ? $application->toArray() : false,
            'completed' => $completed,
            'name' => $name,
            'title' => $user->name().' :: Volunteers',
            'volunteersPage' => true,
            'hours' => $hours,
        ]);
    }

/*
 --- Admin: Hours ---
 */

    public function hoursBrowse($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $limit = 100;
        $page = max(0, $req->query('page'));
        $showApproved = $req->query('approved');
        if ($showApproved === null) {
            $showApproved = true;
        }

        $hours = VolunteerHour::where('organization', $org->id())
            ->where('approved', $showApproved)
            ->sort($showApproved ? 'timestamp DESC' : 'timestamp ASC')
            ->start($page * $limit)
            ->first($limit);
        $count = count($hours);

        return new View('hours/browse', [
            'org' => $org,
            'title' => 'Volunteer Hours',
            'hoursPage' => true,
            'showApproved' => $showApproved,
            'hours' => $hours,
            'hasLess' => $page > 0,
            'hasMore' => $count > $limit * ($page + 1),
            'page' => $page,
            'count' => $count,
            'numAdded' => $req->params('numAdded'),
            'numVolunteers' => $req->params('numVolunteers'),
        ]);
    }

    public function recordHoursStep1($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $places = VolunteerPlace::where('organization', $org->id())
            ->sort('name ASC')
            ->all();

        return new View('hours/add', [
            'org' => $org,
            'title' => 'Add Volunteer Hours',
            'hoursPage' => true,
            'places' => $places,
        ]);
    }

    public function recordHoursStep2($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $place = $req->query('place');
        if ($place) {
            $place = new VolunteerPlace($place);
            if (!$place->exists()) {
                $place = false;
            }
        }

        if (!$place) {
            return $res->redirect($org->manageUrl().'/hours/add');
        }

        // check for previous input
        $input = ($req->request('json')) ? json_decode($req->request('json'), true) : $req->request();

        $start = strtotime('-5 days');
        $end = strtotime('today');

        $days = $this->daysArray($start, $end);

        // recreate days from input
        if (isset($input['days'])) {
            $days = [];
            foreach ($input['days'] as $k => $day) {
                $date = \DateTime::createFromFormat('M j, Y', $day);
                if ($date) {
                    $days[] = $this->dayArrayFromTs($date->getTimestamp());
                }
            }
        }

        $volunteers = Volunteer::where('organization', $org->id())
            ->where('role', Volunteer::ROLE_VOLUNTEER, '>=')
            ->sort('uid ASC')
            ->all();

        $db = $this->getApp()['database']->getDefault();
        $availableTags = (array) $db->select('tag')
            ->from('VolunteerHourTags')
            ->where('organization', $org->id())
            ->orderBy('RAND()')
            ->groupBy('tag')
            ->limit(10)
            ->column();

        return new View('hours/add2', [
            'org' => $org,
            'title' => 'Add Volunteer Hours',
            'hoursPage' => true,
            'days' => $days,
            'input' => $input,
            'volunteers' => $volunteers,
            'place' => $place,
            'tags' => array_value($input, 'tags'),
            'availableTags' => $availableTags,
        ]);
    }

    public function recordHoursStep3($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $place = $req->query('place');
        if ($place) {
            $place = new VolunteerPlace($place);
        }

        if (!$place || !$place->exists()) {
            return $res->redirect($org->manageUrl().'/hours/add');
        }

        $input = $req->request();
        $totals = [];

        // recreate days and filter out empty entries
        $days = [];
        $remove = [];
        foreach ($input['days'] as $k => $day) {
            $date = \DateTime::createFromFormat('M j, Y', $day);
            if ($date) {
                $days[] = $this->dayArrayFromTs($date->getTimestamp());
            } else {
                $remove[] = $k;
            }
        }

        // reverse sort remove so that keys are in descending order for deletion
        sort($remove);
        array_reverse($remove);

        // remove empty day entries
        foreach ($remove as $k) {
            unset($input['days'][$k]);
        }

        foreach ($input['hours'] as $uid => $hours) {
            // remove empty hour entries
            foreach ($remove as $k) {
                unset($input['hours'][$uid][$k]);
                unset($hours[$k]);
            }

            // remove empty volunteer and hour entries
            $total = array_sum($hours);
            if ($total == 0) {
                unset($input['hours'][$uid]);
                unset($input['username'][$uid]);
            } else {
                $totals[$uid] = $total;
            }
        }

        // no entries?
        if (empty($input['hours'])) {
            return $this->recordHoursStep2($req, $res);
        }

        // validate tags
        $tags = $req->request('tags');
        foreach ((array) explode(' ', $tags) as $tag) {
            if (!preg_match('/^[A-Za-z0-9_-]*$/', $tag)) {
                $this->app['errors']->add('invalid_volunteer_hour_tags');

                return $this->recordHoursStep2($req, $res);
            }
        }

        return new View('hours/add3', [
            'org' => $org,
            'title' => 'Confirm Volunteer Hours',
            'hoursPage' => true,
            'input' => $input,
            'days' => $days,
            'json' => str_replace("'", '', json_encode($input)),
            'totals' => $totals,
            'place' => $place,
            'tags' => $tags,
        ]);
    }

    public function recordHoursStep4($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        // go back to the edit screen
        if ($req->request('edit')) {
            return $this->recordHoursStep2($req, $res);
        }

        $place = new VolunteerPlace($req->query('place'));

        // decode hours from json
        $input = json_decode($req->request('json'), true);

        // create days
        $days = [];
        foreach ($input['days'] as $d => $day) {
            $date = \DateTime::createFromFormat('M j, Y', $day);
            $days[$d] = $date->getTimestamp();
        }

        // create hours
        $numHours = 0;
        $volunteers = [];

        foreach ($input['hours'] as $uid => $hours) {
            foreach ($hours as $d => $n) {
                if ($n <= 0) {
                    continue;
                }

                $hourMeta = [
                    'organization' => $org->id(),
                    'hours' => $n,
                    'timestamp' => $days[$d],
                    'place' => $place->id(),
                    'approved' => true,
                    'tags' => $input['tags'],
                    'uid' => $uid, ];

                $hour = new VolunteerHour();

                if ($hour->create($hourMeta)) {
                    if (!isset($volunteers[$uid])) {
                        $volunteers[$uid] = 0;
                    }

                    $volunteers[$uid] += $n;
                    $numHours += $n;
                }
            }
        }

        $req->setParams([
            'numAdded' => $numHours,
            'numVolunteers' => count($volunteers), ]);

        if ($numHours > 0) {
            return $this->hoursBrowse($req, $res);
        } else {
            return $this->recordHoursStep2($req, $res);
        }
    }

    public function hoursView($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $hour, $section) = $lookup;

        return new View('hours/view', [
            'org' => $org,
            'hour' => $hour->toArray(),
            'tags' => $hour->tags(),
            'volunteer' => $hour->volunteer(),
            'place' => $hour->relation('place'),
            'title' => 'Hours Details :: Volunteers',
            'hoursPage' => true,
        ]);
    }

/*
--- Admin: Places ---
*/

    public function placesBrowse($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $limit = 100;
        $page = max(0, $req->query('page'));
        $showApproved = $req->query('approved');
        if ($showApproved === null) {
            $showApproved = true;
        }

        $query = VolunteerPlace::where('organization', $org->id())
            ->sort('name ASC')
            ->start($page * $limit);

        if ($showApproved) {
            $query->where('(place_type = '.VolunteerPlace::INTERNAL.' OR (place_type = '.VolunteerPlace::EXTERNAL.' AND verify_approved = 1))');
        } else {
            $query->where('place_type', VolunteerPlace::EXTERNAL)
                  ->where('verify_approved', false);
        }

        $places = $query->first($limit);
        $count = count($places);

        return new View('places/browse', [
            'org' => $org,
            'title' => 'Places',
            'placesPage' => true,
            'places' => $places,
            'hasLess' => $page > 0,
            'hasMore' => $count > $limit * ($page + 1),
            'page' => $page,
            'count' => $count,
            'showApproved' => $showApproved,
            'success' => $req->query('success'), ]);
    }

    public function placesAddForm($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $place = array_replace([
            'name' => '',
            'place_type' => VolunteerPlace::INTERNAL,
            'address' => '', ], $req->request());

        return new View('places/modify', [
            'org' => $org,
            'title' => 'Add Volunteer Places',
            'placesPage' => true,
            'place' => $place,
        ]);
    }

    public function placesAdd($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        $input = $req->request();
        $input['organization'] = $org->id();
        $input['verify_approved'] = true;

        $place = new VolunteerPlace();
        $place->create($input);

        if ($place) {
            $res->redirect($org->manageUrl().'/places?success=t');
        } else {
            return $this->addPlaceForm($req, $res);
        }
    }

    public function placesView($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $place, $section) = $lookup;

        return new View('places/view', [
            'org' => $org,
            'title' => $place->name.' :: Places',
            'placesPage' => true,
            'place' => $place->toArray(), ]);
    }

    public function placesEditForm($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $place, $section) = $lookup;

        return new View('places/modify', [
            'org' => $org,
            'title' => $place->name.' :: Places',
            'placesPage' => true,
            'place' => $place->toArray(), ]);
    }

/*
--- Admin: Generic Model Routes ---
*/

    public function modelEdit($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $model, $section) = $lookup;

        $model->set($req->request());

        $redir = $req->query('redir');
        if ($redir == 'browse') {
            if (in_array($section, ['volunteers', 'hours', 'places'])) {
                $res->redirect($org->manageUrl().'/'.$section.'?approved=0');
            } else {
                $res->redirect($org->manageUrl().'/'.$section);
            }
        } else if (strpos($redir, ',') !== false) {
            list($section, $id) = explode(',', $redir);
            $res->redirect($org->manageUrl()."/$section/$id");
        } else {
            $id = $model->id();

            if ($section == 'volunteers') {
                $id = $model->uid;
            }

            $res->redirect($org->manageUrl().'/'.$section.'/'.$id);
        }
    }

    public function modelDelete($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $model, $section) = $lookup;

        // volunteer coordinator cannot delete themselves
        if ($section == 'volunteers' && $model->uid == $this->app['user']->id()) {
            return $res->redirect($org->manageUrl().'/'.$section.'?error=cannot_delete_self');
        }

        $model->delete();

        if ($section == 'hours') {
            $res->redirect($org->manageUrl().'/'.$section.'?approved=1');
        } else {
            $res->redirect($org->manageUrl().'/'.$section);
        }
    }

/*
--- Admin: Reports ---
*/

    public function reports($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        return new View('reports', [
            'org' => $org,
            'title' => 'Reports',
            'reportsPage' => true,
            'reports' => Report::$availableReports,
            'firstHourTs' => $org->firstHourTimestamp(),
        ]);
    }

/*
--- Admin: Helpers ---
*/

    private function getOrgForAdmin($req, $res)
    {
        $org = $this->getOrg($req, $res);

        if (is_object($org)) {
            if ($org->can('admin', $this->app['user'])) {
                // calculate the number of unapproved volunteers, places, and hours
                $unapprovedVolunteers = Volunteer::where('organization', $org->id())
                    ->where('role', Volunteer::ROLE_AWAITING_APPROVAL)
                    ->count();

                $unapprovedHours = VolunteerHour::where('organization', $org->id())
                    ->where('approved', false)
                    ->count();

                $unapprovedPlaces = VolunteerPlace::where('organization', $org->id())
                    ->where('place_type', VolunteerPlace::EXTERNAL)
                    ->where('verify_approved', false)
                    ->count();

                $this->app['view_engine']->setGlobalParameters([
                    'volunteersAwaitingApproval' => $unapprovedVolunteers,
                    'hoursAwaitingApproval' => $unapprovedHours,
                    'placesAwaitingApproval' => $unapprovedPlaces, ]);
            } else {
                $res->setCode(401);

                return false;
            }
        }

        return $org;
    }

    private function getModelForAdmin($req, $res)
    {
        // lookup model class
        // index derived from /organizations/:username/admin/SECTION/....
        $section = $req->paths(3);
        $modelClass = array_value(self::$sectionModels, $section);

        if (!$modelClass) {
            $res->setCode(404);

            return false;
        }

        // lookup org
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return false;
        }

        $model = new $modelClass($req->params('id'));

        if ($section == 'volunteers') {
            $model = new $modelClass([$req->params('id'), $org->id()]);
        }

        if (!$model->exists()) {
            $res->setCode(404);

            return false;
        }

        if (!$model->can('view', $this->app['user'])) {
            $res->setCode(401);

            return false;
        }

        return [
            $org,
            $model,
            $section, ];
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

    private function daysArray($start, $end)
    {
        $days = [];

        $t = $end;
        while ($t > $start) {
            $days[] = $this->dayArrayFromTs($t);

            // subtract 1 day
            $t -= 86400;
        }

        $days = array_reverse($days);

        return $days;
    }

    private function dayArrayFromTs($t)
    {
        return [
            'timestamp' => date('l', $t),
            'date' => date('M j, Y', $t),
            'today' => date('Ymd') == date('Ymd', $t),
            'yesterday' => date('Ymd', strtotime('yesterday')) == date('Ymd', $t),
            'ts' => $t, ];
    }
}
