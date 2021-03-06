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
use ICanBoogie\Inflector;
use Infuse\HasApp;
use Infuse\Utility as U;
use Infuse\View;
use Pulsar\Query;

class Controller
{
    use HasApp;

    const PER_PAGE = 10;

    static $sectionModels = [
        'volunteers' => Volunteer::class,
        'hours' => VolunteerHour::class,
        'places' => VolunteerPlace::class,
    ];

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

        return new View('@admin/dashboard', [
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

        // build the query
        $query = Volunteer::where('organization', $org->id())
            ->join(User::class, 'uid', 'id')
            ->with('uid');

        $role = $req->query('role');

        $showInactive = $req->query('inactive');
        if ($showInactive !== null) {
            $showInactive = (bool) $showInactive;
        }

        if ($showInactive === false) {
            $query->where('active', true);
        } else if ($showInactive === true) {
            $query->where('active', false);
        }

        $tab = 'all';
        if ($role !== null) {
            $query->where('role', $role);

            if ($role == Volunteer::ROLE_VOLUNTEER) {
                $tab = 'approved';
            } else if ($role == Volunteer::ROLE_AWAITING_APPROVAL) {
                $tab = 'pending';
            }
        }

        // search
        $search = $req->query('search');
        $searchParams = ['Users.full_name', 'Users.username', 'Users.email'];
        $this->addSearch($search, $searchParams, $query);

        // sorting
        $sort = $req->query('sort');
        if (!$sort) {
            $sort = 'Users.full_name asc';
        }
        $query->sort($sort);

        // pagination
        $perPage = $req->query('per_page') ?? self::PER_PAGE;
        $page = max(1, $req->query('page'));
        $queryStr = $req->query();

        $queryStrNoPage = $queryStr;
        if (isset($queryStrNoPage['page'])) {
            unset($queryStrNoPage['page']);
        }
        $queryStrNoPage = http_build_query($queryStrNoPage);

        $queryStrNoSort = $queryStr;
        if (isset($queryStrNoSort['page'])) {
            unset($queryStrNoSort['page']);
        }
        $queryStrNoSort = http_build_query($queryStrNoSort);

        // fetch the results
        $volunteers = $query->start(($page-1) * $perPage)->first($perPage);
        $count = $query->count();
        $numPages = ceil($count / $perPage);

        if ($req->query('error') == 'cannot_delete_self') {
            $this->app['errors']->add('As a volunteer coordinator, you cannot remove yourself.');
        }

        return new View('@admin/volunteers/browse', [
            'org' => $org,
            'title' => 'Volunteers',
            'volunteersPage' => true,
            'volunteers' => $volunteers,
            'showInactive' => $showInactive,
            'tab' => $tab,
            'role' => $role,
            'hasLess' => $page > 1,
            'hasMore' => $page < $numPages,
            'page' => $page,
            'numPages' => $numPages,
            'count' => $count,
            'numAdded' => $req->params('numAdded'),
            'req' => $req,
            'queryStrNoPage' => $queryStrNoPage,
            'queryStrNoSort' => $queryStrNoSort,
            'sort' => $sort,
            'search' => $search,
        ]);
    }

    public function addVolunteerForm($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        return new View('@admin/volunteers/add', [
            'org' => $org,
            'title' => 'Add Volunteers',
            'volunteersPage' => true,
            'emails' => $req->request('emails'),
            'numAdded' => $req->params('numAdded'),
            'req' => $req,
        ]);
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

                                    // add any metadata to volunteer
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

        return new View('@admin/volunteers/import', [
            'org' => $org,
            'title' => 'Import Volunteers',
            'volunteersPage' => true,
            'error' => $req->params('error'),
            'success' => $req->params('success'),
            'numAdded' => $req->params('numAdded'),
            'req' => $req
        ]);
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

        $query = $org->getHoursQuery()
            ->where('uid', $volunteer->uid);
        $hours = $query->first(10);
        $totalHours = $query->sum('hours');
        $name = $user->name(true);

        $metadata = [];
        if ($volunteer->metadata) {
            $inflector = Inflector::get();
            foreach ($volunteer->metadata as $key => $value) {
                $title = $inflector->titleize(str_replace(['.', '_'], [' ', ' '], $key));
                $metadata[] = ['title' => $title, 'value' => $value];
            }
        }

        return new View('@admin/volunteers/view', [
            'org' => $org,
            'volunteer' => $volunteer->toArray(),
            'user' => $user,
            'application' => ($application) ? $application->toArray() : false,
            'completed' => $completed,
            'name' => $name,
            'title' => $name.' :: Volunteers',
            'volunteersPage' => true,
            'metadata' => $metadata,
            'hours' => $hours,
            'totalHours' => $totalHours,
            'req' => $req,
            'inviteSent' => $req->params('inviteSent')
        ]);
    }

    function sendVolunteerInvite($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $volunteer, $section) = $lookup;

        $user = $volunteer->relation('uid');
        $org->sendInvite($user);

        $req->setParams(['inviteSent' => true]);

        return $this->volunteersView($req, $res);
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

        // build the query
        $query = VolunteerHour::where('organization', $org->id())
            ->join(User::class, 'uid', 'id')
            ->join(VolunteerPlace::class, 'place', 'id')
            ->with('uid')
            ->with('place');

        $showApproved = $req->query('approved');
        if ($showApproved !== null) {
            $showApproved = (bool) $showApproved;
        }

        $tab = 'all';
        if ($showApproved === true) {
            $query->where('approved', true);
            $tab = 'approved';
        } else if ($showApproved === false) {
            $query->where('approved', false);
            $tab = 'pending';
        }

        // search
        $search = $req->query('search');
        $searchParams = ['Users.full_name', 'Users.username', 'Users.email', 'VolunteerPlaces.name'];
        $this->addSearch($search, $searchParams, $query);

        // sorting
        $sort = $req->query('sort');
        if (!$sort) {
            $sort = 'timestamp desc';
        }
        $query->sort($sort);

        // pagination
        $perPage = $req->query('per_page') ?? self::PER_PAGE;
        $page = max(1, $req->query('page'));
        $queryStr = $req->query();

        $queryStrNoPage = $queryStr;
        if (isset($queryStrNoPage['page'])) {
            unset($queryStrNoPage['page']);
        }
        $queryStrNoPage = http_build_query($queryStrNoPage);

        $queryStrNoSort = $queryStr;
        if (isset($queryStrNoSort['sort'])) {
            unset($queryStrNoSort['sort']);
        }
        $queryStrNoSort = http_build_query($queryStrNoSort);

        // fetch the results
        $hours = $query->start(($page-1) * $perPage)->first($perPage);
        $count = $query->count();
        $numPages = ceil($count / $perPage);

        return new View('@admin/hours/browse', [
            'org' => $org,
            'title' => 'Volunteer Hours',
            'hoursPage' => true,
            'showApproved' => $showApproved,
            'tab' => $tab,
            'hours' => $hours,
            'hasLess' => $page > 1,
            'hasMore' => $page < $numPages,
            'page' => $page,
            'numPages' => $numPages,
            'count' => $count,
            'numAdded' => $req->params('numAdded'),
            'numVolunteers' => $req->params('numVolunteers'),
            'req' => $req,
            'queryStrNoPage' => $queryStrNoPage,
            'queryStrNoSort' => $queryStrNoSort,
            'sort' => $sort,
            'search' => $search
        ]);
    }

    public function recordHoursStep1($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        // get the volunteer
        $uid = $req->query('user');
        $volunteer = Volunteer::find([$uid, $org->id()]);
        if (!$volunteer) {
            return $res->setCode(404);
        }

        $places = VolunteerPlace::where('organization', $org->id())
            ->sort('name ASC')
            ->all();

        return new View('@admin/hours/add1', [
            'org' => $org,
            'title' => 'Add Volunteer Hours',
            'hoursPage' => true,
            'places' => $places,
            'req' => $req,
            'volunteer' => $volunteer
        ]);
    }

    public function recordHoursStep2($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        // get the volunteer
        $uid = $req->query('user');
        $volunteer = Volunteer::find([$uid, $org->id()]);
        if (!$volunteer) {
            return $res->setCode(404);
        }

        $volunteers = [$volunteer];

        // get the place
        $place = VolunteerPlace::find($req->query('place'));
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

        if (!isset($input['hours'])) {
            $input['hours'] = [];
            foreach ($volunteers as $volunteer) {
                $input['hours'][$volunteer->uid] = [];
            }
        }

        $db = $this->getApp()['database']->getDefault();
        $availableTags = (array) $db->select('tag')
            ->from('VolunteerHourTags')
            ->where('organization', $org->id())
            ->orderBy('RAND()')
            ->groupBy('tag')
            ->limit(10)
            ->column();

        return new View('@admin/hours/add2', [
            'org' => $org,
            'title' => 'Add Volunteer Hours',
            'hoursPage' => true,
            'days' => $days,
            'input' => $input,
            'volunteers' => $volunteers,
            'place' => $place,
            'tags' => array_value($input, 'tags'),
            'availableTags' => $availableTags,
            'req' => $req
        ]);
    }

    public function recordHoursStep3($req, $res)
    {
        $org = $this->getOrgForAdmin($req, $res);

        if (!is_object($org)) {
            return;
        }

        // get the place
        $place = VolunteerPlace::find($req->query('place'));
        if (!$place) {
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

        return new View('@admin/hours/add3', [
            'org' => $org,
            'title' => 'Confirm Volunteer Hours',
            'hoursPage' => true,
            'input' => $input,
            'days' => $days,
            'json' => str_replace("'", '', json_encode($input)),
            'totals' => $totals,
            'place' => $place,
            'tags' => $tags,
            'req' => $req
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

        return new View('@admin/hours/view', [
            'org' => $org,
            'hour' => $hour->toArray(),
            'tags' => $hour->tags(),
            'volunteer' => $hour->volunteer(),
            'place' => $hour->place(),
            'title' => 'Hours Details :: Volunteers',
            'hoursPage' => true,
            'req' => $req
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

        // build the query
        $query = VolunteerPlace::where('organization', $org->id());

        $showApproved = $req->query('approved');
        if ($showApproved !== null) {
            $showApproved = (bool) $showApproved;
        }

        $tab = 'all';
        if ($showApproved === true) {
            $query->where('(place_type = '.VolunteerPlace::INTERNAL.' OR (place_type = '.VolunteerPlace::EXTERNAL.' AND verify_approved = 1))');
            $tab = 'approved';
        } else if ($showApproved === false) {
            $query->where('place_type', VolunteerPlace::EXTERNAL)
                ->where('verify_approved', false);
            $tab = 'pending';
        }

        // search
        $search = $req->query('search');
        $searchParams = ['name', 'verify_name', 'verify_email'];
        $this->addSearch($search, $searchParams, $query);

        // sorting
        $sort = $req->query('sort');
        if (!$sort) {
            $sort = 'name asc';
        }
        $query->sort($sort);

        // pagination
        $perPage = $req->query('per_page') ?? self::PER_PAGE;
        $page = max(1, $req->query('page'));
        $queryStr = $req->query();

        $queryStrNoPage = $queryStr;
        if (isset($queryStrNoPage['page'])) {
            unset($queryStrNoPage['page']);
        }
        $queryStrNoPage = http_build_query($queryStrNoPage);

        $queryStrNoSort = $queryStr;
        if (isset($queryStrNoSort['sort'])) {
            unset($queryStrNoSort['sort']);
        }
        $queryStrNoSort = http_build_query($queryStrNoSort);

        // fetch the results
        $places = $query->start(($page-1) * $perPage)->first($perPage);
        $count = $query->count();
        $numPages = ceil($count / $perPage);

        return new View('@admin/places/browse', [
            'org' => $org,
            'title' => 'Places',
            'placesPage' => true,
            'places' => $places,
            'tab' => $tab,
            'hasLess' => $page > 1,
            'hasMore' => $page < $numPages,
            'page' => $page,
            'numPages' => $numPages,
            'count' => $count,
            'showApproved' => $showApproved,
            'success' => $req->query('success'),
            'req' => $req,
            'queryStrNoPage' => $queryStrNoPage,
            'queryStrNoSort' => $queryStrNoSort,
            'sort' => $sort,
            'search' => $search,
        ]);
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

        return new View('@admin/places/modify', [
            'org' => $org,
            'title' => 'Add Volunteer Places',
            'placesPage' => true,
            'place' => $place,
            'req' => $req
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

        $query = $org->getHoursQuery()
            ->where('place', $place->id());
        $hours = $query->first(10);

        return new View('@admin/places/view', [
            'org' => $org,
            'title' => $place->name.' :: Places',
            'placesPage' => true,
            'place' => $place->toArray(),
            'req' => $req,
            'hours' => $hours
        ]);
    }

    public function placesEditForm($req, $res)
    {
        $lookup = $this->getModelForAdmin($req, $res);

        if (!$lookup) {
            return;
        }

        list($org, $place, $section) = $lookup;

        return new View('@admin/places/modify', [
            'org' => $org,
            'title' => $place->name.' :: Places',
            'placesPage' => true,
            'place' => $place->toArray(),
            'req' => $req
        ]);
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

        return new View('@admin/reports', [
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

        if ($section == 'volunteers') {
            $model = $modelClass::find([$req->params('id'), $org->id()]);
        } else {
            $model = $modelClass::find($req->params('id'));
        }

        if (!$model) {
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
            $section,
        ];
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

    /**
     * Adds a search term to a query.
     *
     * @param $search
     * @param array $searchParams
     * @param Query $query
     */
    private function addSearch($search, array $searchParams, Query $query)
    {
        if (preg_match('/^[a-z0-9\s@\-\.\+]+$/i', $search)) {
            $search = trim($search);
            $conditions = [];
            foreach ($searchParams as $k) {
                $conditions[] = "$k LIKE \"%$search%\"";
            }
            $query->where('(' . implode(' OR ', $conditions) . ')');
        }
    }
}
