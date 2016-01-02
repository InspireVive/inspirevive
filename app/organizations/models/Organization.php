<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\organizations\models;

use infuse\Database;
use infuse\Model;
use infuse\Utility as U;
use infuse\Validate;
use app\users\models\User;
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerHour;
use app\volunteers\models\VolunteerPlace;

class Organization extends Model
{
    public static $autoTimestamps;
    public static $scaffoldApi;

    public static $properties = [
        'name' => [
            'type' => 'string',
            'required' => true,
            'searchable' => true,
        ],
        'email' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'email',
            'admin_hidden_property' => true,
            'admin_html' => '<a href="mailto:{email}">{email}</a>',
            'searchable' => true,
        ],
        'username' => [
            'type' => 'string',
            'required' => true,
            'unique' => true,
            'admin_hidden_property' => true,
        ],

        /* computed properties */

        'unapproved_hours_notify_count' => [
            'type' => 'number',
            'default' => 0,
            'mutable' => false,
            'admin_hidden_property' => true,
       ],
    ];

    protected function hasPermission($permission, Model $requester)
    {
        return $requester->isAdmin();
    }

    //////////////////////
    // HOOKS
    //////////////////////

    protected function postDeleteHook()
    {
        // nuke all volunteers, hours, etc...
        $nuke = [
            'Volunteers',
            'VolunteerHourTags',
            'VolunteerHours',
            'VolunteerPlaces', ];

        foreach ($nuke as $tablename) {
            Database::delete(
                $tablename,
                [
                    'organization' => $this->_id, ]);
        }
    }

    //////////////////////
    // GETTERS
    //////////////////////

    /**
     * Gets the role of the user in the organization.
     *
     * @param User $user
     *
     * @return int
     */
    public function getRoleOfUser(User $user)
    {
        $volunteer = new Volunteer([$user->id(), $this->_id]);

        return ($volunteer->exists()) ? $volunteer->role : Volunteer::ROLE_NONE;
    }

    /**
     * Generates the set of WHERE parameters to look up hours
     * from this organization based on whether outside hours are visible.
     *
     * @param string $prefix optional prefix of the hours table
     *
     * @return array
     */
    public function hourWhereParams($prefix = '')
    {
        if (!empty($prefix)) {
            $prefix .= '.';
        }

        $where = [
            $prefix.'uid IN ( SELECT uid FROM Volunteers WHERE organization = '.$this->id().' AND role >= '.Volunteer::ROLE_VOLUNTEER.' )',
       ];

        $where[$prefix.'organization'] = $this->id();

        return $where;
    }

    /**
     * Generates the URL for the organization profile.
     *
     * @return string
     */
    public function url()
    {
        return $this->app['base_url'].'organizations/'.$this->username;
    }

    /**
     * Generates the URL to the management dashboard.
     *
     * @return string
     */
    public function manageUrl()
    {
        return $this->url().'/admin';
    }

    /**
     * Gets the most recent volunteer hours performed by volunteers
     * or a specific volunteer in this organization.
     *
     * @param int       $start     optional start timestamp bound
     * @param int       $end       optional end timestamp bound
     * @param Volunteer $volunteer optional volunteer to filter hours with
     *
     * @return Model\Iterator
     */
    public function hours($start = false, $end = false, Volunteer $volunteer = null)
    {
        if (!$start) {
            $start = 0;
        }

        if (!$end) {
            $end = time();
        }

        $where = $this->hourWhereParams();

        $where[] = 'timestamp >= '.$start;
        $where[] = 'timestamp <= '.$end;

        if ($volunteer) {
            $where['uid'] = $volunteer->id();
        }

        return VolunteerHour::findAll([
            'where' => $where,
            'sort' => 'timestamp DESC', ]);
    }

    /**
     * Returns a list of tags used for approved volunteer hours within
     * this organization.
     *
     * @return array
     */
    public function hourTags()
    {
        return (array) Database::select(
            'VolunteerHourTags t JOIN VolunteerHours h ON t.hour = h.id',
            't.tag',
            [
                'where' => [
                    't.organization' => $this->id(),
                    'h.approved' => true, ],
                'fetchStyle' => 'singleColumn',
                'orderBy' => 't.tag ASC',
                'groupBy' => 't.tag', ]);

        return $tags;
    }

    public function alerts()
    {
        $alerts = [];

        $locale = $this->app['locale'];

        $unapproved = $this->numUnapprovedVolunteers();
        if ($unapproved > 0) {
            $alerts[] = [
                'level' => 'info',
                'message' => $unapproved.$locale->p($unapproved, ' volunteer', ' volunteers').' are awaiting approval!',
                'href' => 'volunteers?status=unapproved',
           ];
        }

        $unapproved = $this->numUnapprovedHours();
        if ($unapproved > 0) {
            $alerts[] = [
                'level' => 'info',
                'message' => $unapproved.$locale->p($unapproved, ' volunteer hour', ' volunteer hours').' are awaiting approval!',
                'href' => 'hours?status=unapproved',
           ];
        }

        $unapproved = $this->numUnapprovedPlaces();
        if ($unapproved > 0) {
            $alerts[] = [
                'level' => 'info',
                'message' => $unapproved.$locale->p($unapproved, ' volunteer place', ' volunteer places').' are awaiting approval!',
                'href' => 'places?status=unapproved',
           ];
        }

        return $alerts;
    }

    //////////////////////
    // STATISTICS
    //////////////////////

    public function numUnapprovedVolunteers()
    {
        return Volunteer::totalRecords([
            'organization' => $this->id(),
            'role' => Volunteer::ROLE_AWAITING_APPROVAL, ]);
    }

    public function numUnapprovedHours()
    {
        return VolunteerHour::totalRecords([
            'organization' => $this->id(),
            'approved' => false, ]);
    }

    public function numUnapprovedPlaces()
    {
        return VolunteerPlace::totalRecords([
            'organization' => $this->id(),
            'place_type' => VolunteerPlace::EXTERNAL,
            'verify_approved' => false, ]);
    }

    /**
     * Gets the total number of volunteers associated with this organization
     * during a period.
     *
     * @param int $start begin timestamp
     * @param int $start end timestamp
     *
     * @return int
     */
    public function numVolunteers($start = false, $end = false)
    {
        if (!$start) {
            $start = 0;
        }

        if (!$end) {
            $end = time();
        }

        return Volunteer::totalRecords([
            'organization' => $this->id(),
            'created_at >= '.$start,
            'created_at <= '.$end,
            'role >= '.Volunteer::ROLE_VOLUNTEER, ]);
    }

    /**
     * Gets the top N volunteers within a time period.
     *
     * @param int $n     number of places requested
     * @param int $start begin timestamp
     * @param int $start end timestamp
     *
     * @return array(User)
     */
    public function topVolunteers($n = 5, $start = false, $end = false)
    {
        if (!$start) {
            $start = 0;
        }

        if (!$end) {
            $end = time();
        }

        $topVolunteers = [];

        $where = $this->hourWhereParams();

        $where[] = 'timestamp >= '.$start;
        $where[] = 'timestamp <= '.$end;
        $where['approved'] = true;

        $topVolunteers = Database::select(
            'VolunteerHours',
            'uid,SUM(hours) as total_hours',
            [
                'where' => $where,
                'orderBy' => 'total_hours DESC',
                'limit' => "0,$n",
                'groupBy' => 'uid', ]);

        $return = [];

        foreach ($topVolunteers as $info) {
            $volunteer = [
                'hours' => $info['total_hours'],];

            if (isset($info['uid'])) {
                $volunteer['user'] = new User($info['uid']);
            }

            $return[] = $volunteer;
        }

        return $return;
    }

    /**
     * Gets the toal number of hours volunteered.
     *
     * @param int       $start
     * @param int       $end
     * @param Volunteer $volunteer
     *
     * @return int
     */
    public function totalHoursVolunteered($start = false, $end = false, Volunteer $volunteer = null)
    {
        $where = $this->hourWhereParams();

        if (!$start) {
            $start = 0;
        }

        if (!$end) {
            $end = time() + 3600;
        }

        $where['approved'] = true;
        $where[] = 'timestamp >= '.$start;
        $where[] = 'timestamp <= '.$end;

        if ($volunteer) {
            $where['uid'] = $volunteer->id();
        }

        return (int) Database::select(
            'VolunteerHours',
            'SUM(hours)',
            [
                'where' => $where,
                'single' => true, ]);
    }

    /**
     * Gets the total number of hours volunteered broken down by tags.
     *
     * @param int       $start
     * @param int       $end
     * @param Volunteer $volunteer
     *
     * @return array
     */
    public function totalHoursVolunteeredByTag($start = false, $end = false, Volunteer $volunteer = null)
    {
        $where = $this->hourWhereParams('h');

        if (!$start) {
            $start = 0;
        }

        if (!$end) {
            $end = time() + 3600;
        }

        $where['h.approved'] = true;
        $where[] = 'h.timestamp >= '.$start;
        $where[] = 'h.timestamp <= '.$end;

        if ($volunteer) {
            $where['h.uid'] = $volunteer->id();
        }

        $totalsByTag = (array) Database::select(
            'VolunteerHourTags t LEFT JOIN VolunteerHours h ON t.hour=h.id',
            't.tag AS tag, SUM(h.hours) AS hours',
            [
                'where' => $where,
                'groupBy' => 't.tag', ]);

        $tags = [];
        foreach ($totalsByTag as $row) {
            $tags[$row['tag']] = (int) $row['hours'];
        }

        return $tags;
    }

    public function activityForPeriod($start, $end)
    {
        date_default_timezone_set('UTC');

        // start period at the beginning of the month and end period at the end of the month
        $start = mktime(0, 0, 0, date('n', $start), 1, date('Y', $start));
        $end = mktime(23, 59, 0, date('n', $end), date('t', $end), date('Y', $end));

        // Compute the total number of volunteers who contributed
        $where = $this->hourWhereParams();
        $where[] = 'timestamp >= '.$start;
        $where[] = 'timestamp <= '.$end;
        $where['approved'] = true;

        $totalVolunteers = Database::select(
            'VolunteerHours',
            'COUNT(DISTINCT uid)',
            [
                'where' => $where,
                'single' => true, ]);

        // Compute time series data where each period = 1 month
        $timeSeries = [];
        $totalHours = 0;

        $t = $start;
        while ($t < $end) {
            $periodStart = mktime(0, 0, 0, date('n', $t), 1, date('Y', $t));
            $periodEnd = mktime(23, 59, 0, date('n', $t), date('t', $t), date('Y', $t));

            $hours = $this->totalHoursVolunteered($periodStart, $periodEnd);
            $totalHours += $hours;

            $timeSeries[] = [
                'timestamp' => $periodStart,
                'hours' => $hours,];

            $t = strtotime('+1 month', $t);
        }

        // Compute the top volunteers
        $topVolunteers = $this->topVolunteers(5, $start, $end);
        foreach ($topVolunteers as $k => $volunteer) {
            if (isset($volunteer['user'])) {
                $topVolunteers[$k]['user'] = $volunteer['user']->toArray();
            }
        }

        return [
            'totals' => [
                'hours' => $totalHours,
                'volunteers' => $totalVolunteers,],
            'topVolunteers' => $topVolunteers,
            'timeseries' => $timeSeries,];
    }

    /**
     * Gets the total number of volunteer places associated with this organization
     * during a period.
     *
     * @param int $start begin timestamp
     * @param int $start end timestamp
     *
     * @return int
     */
    public function numPlaces($start = false, $end = false)
    {
        if (!$start) {
            $start = 0;
        }

        if (!$end) {
            $end = time();
        }

        return VolunteerPlace::totalRecords([
            'organization' => $this->id(),
            'created_at >= '.$start,
            'created_at <= '.$end, ]);
    }

    /**
     * Gets the timestamp of the first volunteer hour.
     * If no hours have been created yet, will use the creation date.
     *
     * @return int
     */
    public function firstHourTimestamp()
    {
        $firstHourTs = (int) Database::select(
            'VolunteerHours',
            'MIN(timestamp)',
            [
                'where' => $this->hourWhereParams(),
                'single' => true, ]);

        if ($firstHourTs <= 0) {
            $firstHourTs = $this->created_at;
        }

        // the first  hour timestamp cannot be older than InspireVive
        $firstHourTs = max(
            mktime(0, 0, 0, 4, 1, 2013),
            $firstHourTs);

        return $firstHourTs;
    }

    /**
     * Gets all models with unapproved hour notifications.
     *
     * @return ModelIterator
     */
    public static function orgsWithUnapprovedHourNotifications()
    {
        return static::findAll([
            'where' => [
                'unapproved_hours_notify_count > 0', ], ]);
    }

    //////////////////////
    // SETTERS
    //////////////////////

    /**
     * Adds a volunteer to the organization. If the volunteer is not a
     * member yet, then a temporary account will be created. This
     * will send an e-mail to the user.
     *
     * @param string $emailOrUsername
     *
     * @return Volunteer|false invited volunteer
     */
    public function inviteVolunteer($emailOrUsername)
    {
        $user = false;
        $isEmail = true;
        if (Validate::is($emailOrUsername, 'email')) {
            $user = User::findOne([
                'where' => [
                    'user_email' => $emailOrUsername, ], ]);

            $isEmail = true;
        } else {
            $user = User::findOne([
                'where' => [
                    'username' => $emailOrUsername, ], ]);
        }

        // create temporary user
        if (!$user && $isEmail) {
            $user = User::createTemporary([
                'user_email' => $emailOrUsername,
                'invited_by' => $this->id(), ]);
        }

        if (!$user) {
            return false;
        }

        $isTemporary = $user->isTemporary();

        $volunteer = new Volunteer([$user->id(), $this->id()]);

        if ($volunteer->exists()) {
            return $volunteer;
        }

        $volunteer = new Volunteer();
        $volunteer->grantAllPermissions();
        $volunteer->create([
            'uid' => $user->id(),
            'organization' => $this->id(),
            'application_shared' => true,
            'active' => true,
            'role' => Volunteer::ROLE_VOLUNTEER, ]);

        $base = $this->app['base_url'];

        $orgName = $this->name;
        $ctaUrl = ($isTemporary) ?
            $base.'signup?user_email='.$user->user_email :
            $base.'profile';

        $user->sendEmail(
            'volunteer-invite',
            [
                'subject' => "$orgName has invited you as a volunteer",
                'orgname' => $orgName,
                'cta_url' => $ctaUrl, ]);

        return $volunteer;
    }

    //////////////////////
    // UTILITIES
    //////////////////////

    /**
     * Sends an e-mail notification about unapproved hours.
     *
     * @return bool
     */
    public function sendUnapprovedNotification()
    {
        $unapprovedCount = $this->unapproved_hours_notify_count;

        if ($unapprovedCount == 0) {
            return false;
        }

        $success = $this->sendEmail('unapproved-hours', [
            'subject' => 'Volunteer hours needing your approval for '.$this->name.' on InspireVive',
            'num_unapproved' => $unapprovedCount, ]);

        if ($success) {
            $this->grantAllPermissions();
            $success = $this->set('unapproved_hours_notify_count', 0);
        }

        return $success;
    }

    /**
     * Sends an e-mail to the volunteer organization.
     *
     * @param string $template
     * @param array  $message  details of the message
     *
     * @return bool success
     */
    public function sendEmail($template, $message = [])
    {
        $email = $this->email;
        $orgName = $this->name;

        $message['message'] = $template;
        $message['baseUrl'] = $this->app['base_url'];
        $message['siteEmail'] = $this->app['config']->get('site.email');
        $message['email'] = $email;
        $message['name'] = $orgName;
        $message['orgname'] = $message['name'];
        $message['orgurl'] = $this->url();
        $message['to'] = [
            [
                'email' => $email,
                'name' => $orgName,],];

        if (!isset($message['from_email'])) {
            $message['from_email'] = $this->app['config']->get('site.email');
        }
        if (!isset($message['from_name'])) {
            $message['from_name'] = $this->app['config']->get('site.name');
        }

        return $this->app['mailer']->queueEmail($template, $message);
    }

    /////////////////////////////
    // BACKGROUND TASKS
    /////////////////////////////

    /**
     * Sends unapproved hour notifications.
     *
     * @param bool $echoOutput
     *
     * @return bool
     */
    public static function processUnapprovedNotifications($echoOutput = true)
    {
        $orgs = static::orgsWithUnapprovedHourNotifications();

        foreach ($orgs as $org) {
            if ($org->sendUnapprovedNotification()) {
                if ($echoOutput) {
                    echo '--- Sent notification for org #'.$org->id()."\n";
                }
            }
        }

        return true;
    }
}
