<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Organizations\Models;

use App\Users\Models\User;
use App\Volunteers\Models\Volunteer;
use App\Volunteers\Models\VolunteerHour;
use App\Volunteers\Models\VolunteerPlace;
use Infuse\HasApp;
use Infuse\Utility as U;
use Pulsar\ACLModel;
use Pulsar\Model;

class Organization extends ACLModel
{
    use HasApp;

    public static $properties = [
        'name' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'searchable' => true,
        ],
        'email' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'email',
            'admin_hidden_property' => true,
            'admin_html' => '<a href="mailto:{email}">{email}</a>',
            'searchable' => true,
        ],
        'username' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'unique' => true,
            'admin_hidden_property' => true,
        ],

        /* computed properties */

        'unapproved_hours_notify_count' => [
            'type' => Model::TYPE_INTEGER,
            'default' => 0,
            'admin_hidden_property' => true,
       ],
    ];

    public static $autoTimestamps;

    protected function hasPermission($permission, Model $requester)
    {
        if ($permission == 'create') {
            return true;
        }

        $userRole = $this->getRoleOfUser($requester);
        if ($permission == 'view' && $userRole >= Volunteer::ROLE_VOLUNTEER) {
            return true;
        }

        if (in_array($permission, ['admin', 'edit']) && $userRole == Volunteer::ROLE_ADMIN) {
            return true;
        }

        return $requester->isAdmin();
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
        $volunteer = Volunteer::find([$user->id(), $this->id()]);

        return ($volunteer) ? $volunteer->role : Volunteer::ROLE_NONE;
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
            $prefix.'uid IN ( SELECT uid FROM Volunteers WHERE organization = "'.$this->id().'" AND role >= '.(string) Volunteer::ROLE_VOLUNTEER.' )',
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
        return $this->getApp()['base_url'].'organizations/'.$this->username;
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
     * @return \Pulsar\Iterator
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

        return VolunteerHour::where($where)
            ->sort('timestamp DESC')
            ->all();
    }

    /**
     * Returns a list of tags used for approved volunteer hours within
     * this organization.
     *
     * @return array
     */
    public function hourTags()
    {
        $db = $this->getApp()['database']->getDefault();

        return (array) $db->select('t.tag')
            ->from('VolunteerHourTags t')
            ->join('VolunteerHours h', 't.hour = h.id')
            ->where('t.organization', $this->id())
            ->where('h.approved', true)
            ->orderBy('t.tag ASC')
            ->groupBy('t.tag')
            ->column();
    }

    public function alerts()
    {
        $alerts = [];

        $locale = $this->getApp()['locale'];

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
        return Volunteer::where('organization', $this->id())
            ->where('role', Volunteer::ROLE_AWAITING_APPROVAL)
            ->count();
    }

    public function numUnapprovedHours()
    {
        return VolunteerHour::where('organization', $this->id())
            ->where('approved', false)
            ->count();
    }

    public function numUnapprovedPlaces()
    {
        return VolunteerPlace::where('organization', $this->id())
            ->where('place_type', VolunteerPlace::EXTERNAL)
            ->where('verify_approved', false)
            ->count();
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

        return Volunteer::where('organization', $this->id())
            ->where('created_at', U::unixToDb($start), '>=')
            ->where('created_at', U::unixToDb($end), '<=')
            ->where('role', Volunteer::ROLE_VOLUNTEER, '>=')
            ->count();
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

        $db = $this->getApp()['database']->getDefault();
        $topVolunteers = $db->select('uid,SUM(hours) as total_hours')
            ->from('VolunteerHours')
            ->where($where)
            ->orderBy('total_hours DESC')
            ->limit(0, $n)
            ->groupBy('uid')
            ->all();

        $return = [];

        foreach ($topVolunteers as $info) {
            $volunteer = [
                'hours' => $info['total_hours'],];

            if (isset($info['uid'])) {
                $volunteer['user'] = User::find($info['uid']);
            }

            $return[] = $volunteer;
        }

        return $return;
    }

    /**
     * Gets the total number of hours volunteered.
     *
     * @param int|false       $start
     * @param int|false       $end
     * @param Volunteer|null $volunteer
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

        $query = VolunteerHour::where('approved', true)
            ->where('timestamp', $start, '>=')
            ->where('timestamp', $end, '<=');

        if ($volunteer) {
            $query->where('uid', $volunteer->id());
        }

        return $query->where($where)->sum('hours');
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

        $db = $this->getApp()['database']->getDefault();

        $totalsByTag = (array) $db->select('t.tag AS tag, SUM(h.hours) AS hours')
            ->from('VolunteerHourTags t')
            ->join('VolunteerHours h', 't.hour=h.id', null, 'LEFT JOIN')
            ->where($where)
            ->groupBy('t.tag')
            ->all();

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

        $db = $this->getApp()['database']->getDefault();

        $totalVolunteers = $db->select()
            ->count('DISTINCT uid')
            ->from('VolunteerHours')
            ->where($where)
            ->all();

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
     * Gets the timestamp of the first volunteer hour.
     * If no hours have been created yet, will use the creation date.
     *
     * @return int
     */
    public function firstHourTimestamp()
    {
        $db = $this->getApp()['database']->getDefault();

        $firstHourTs = (int) $db->select()
            ->min('timestamp')
            ->from('VolunteerHours')
            ->where($this->hourWhereParams())
            ->scalar();

        if ($firstHourTs <= 0) {
            $firstHourTs = $this->created_at;
        }

        // the first  hour timestamp cannot be older than InspireVive
        $firstHourTs = max(
            mktime(0, 0, 0, 4, 1, 2013),
            $firstHourTs);

        return $firstHourTs;
    }

    //////////////////////
    // SETTERS
    //////////////////////

    /**
     * Adds a volunteer to the organization. If the volunteer is not a
     * member yet, then a temporary account will be created. This
     * will send an email to the user.
     *
     * @param string $email
     *
     * @throws Exception when the volunteer cannot be invited.
     *
     * @return Volunteer invited volunteer
     */
    public function inviteVolunteer($email)
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address: '.$email);
        }

        $user = User::where('email', $email)->first();

        // create temporary user
        if (!$user) {
            $user = User::createTemporary([
                'email' => $email,
                'invited_by' => $this->id(),
            ]);
        }

        if (!$user) {
            throw new \Exception('Could not locate or create user with email address: '.$email);
        }

        $isTemporary = $user->isTemporary();

        // look for an existing volunteer
        if ($volunteer = Volunteer::find([$user->id(), $this->id()])) {
            return $volunteer;
        }

        // otherwise create one
        $volunteer = new Volunteer();
        $volunteer->uid = $user->id();
        $volunteer->organization = $this->id();
        $volunteer->application_shared = true;
        $volunteer->active = true;
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $volunteer->grantAllPermissions()->saveOrFail();

        $base = $this->getApp()['base_url'];

        $orgName = $this->name;
        $ctaUrl = ($isTemporary) ?
            $base.'signup?email='.$user->email :
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
     * Sends an email notification about unapproved hours.
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
            $this->unapproved_hours_notify_count = 0;
            $success = $this->save();
        }

        return $success;
    }

    /**
     * Sends an email to the volunteer organization.
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

        $app = $this->getApp();

        $message['message'] = $template;
        $message['baseUrl'] = $app['base_url'];
        $message['siteEmail'] = $app['config']->get('app.email');
        $message['email'] = $email;
        $message['name'] = $orgName;
        $message['orgname'] = $message['name'];
        $message['orgurl'] = $this->url();
        $message['to'] = [
            [
                'email' => $email,
                'name' => $orgName,],];

        if (!isset($message['from_email'])) {
            $message['from_email'] = $app['config']->get('app.email');
        }
        if (!isset($message['from_name'])) {
            $message['from_name'] = $app['config']->get('app.title');
        }

        return $app['mailer']->queueEmail($template, $message);
    }
}
