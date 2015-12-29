<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\volunteers\models;

use infuse\Database;
use infuse\Model;
use infuse\Utility as U;
use app\users\models\User;
use app\organizations\models\Organization;

class VolunteerHour extends Model
{
    public static $scaffoldApi;
    public static $autoTimestamps;

    public static $properties = [
        'uid' => [
            'type' => 'number',
            'relation' => 'app\users\models\User',
            'null' => true,
        ],
        'timestamp' => [
            'type' => 'date',
            'required' => true,
            'admin_type' => 'datepicker',
        ],
        'organization' => [
            'type' => 'number',
            'relation' => 'app\organizations\models\Organization',
            'required' => true,
        ],
        'hours' => [
            'type' => 'number',
            'required' => true,
            'validate' => 'numeric',
        ],
        'place' => [
            'type' => 'number',
            'relation' => 'app\volunteers\models\VolunteerPlace',
            'null' => true,
            'admin_hidden_property' => true,
        ],
        'approval_link' => [
            'type' => 'string',
            'null' => true,
        ],
        'approved' => [
            'type' => 'boolean',
            'default' => false,
            'admin_hidden_property' => true,
        ],
        'verification_requested' => [
            'type' => 'boolean',
            'default' => false,
            'admin_hidden_property' => true,
        ],
        'verification_requested_at' => [
            'type' => 'date',
            'null' => true,
            'admin_type' => 'datepicker',
            'admin_hidden_property' => true,
        ],
    ];

    const MINUTES_BETWEEN_EMAILS = 60;

    private $needsApproveEmail;
    private static $createTags;
    private $setTags;

    /**
     * @var string
     */
    private $_deleteEmail;

    /**
     * @var User
     */
    private $_deleteUser;

    /**
     * @var array
     */
    private $_delta;

    protected function hasPermission($permission, Model $requester)
    {
        // find permission verified in find()
        if ($permission == 'find' && $requester->isLoggedIn()) {
            return true;
        }

        // create permission verified in preCreateHook()
        if ($permission == 'create' && $requester->isLoggedIn()) {
            return true;
        }

        // allow user's to edit their own hours (for the purpose of changing balance)
        if ($permission == 'edit' && $requester->id() == $this->relation('uid')->id()) {
            return true;
        }

        $orgModel = $this->relation('organization');

        if (in_array($permission, ['view', 'edit', 'delete']) &&
            $orgModel &&
            $orgModel->getRoleOfUser($requester) == ORGANIZATION_ROLE_ADMIN) {
            return true;
        }

        return $requester->isAdmin();
    }

    public static function find(array $params = [])
    {
        $params['where'] = (array) U::array_value($params, 'where');

        $user = self::$injectedApp['user'];
        if (isset($params['where']['organization']) && !$user->isAdmin()) {
            if (!isset($params['where']['approval_link'])) {
                $org = new Organization($params['where']['organization']);

                if ($org->getRoleOfUser($user) != ORGANIZATION_ROLE_ADMIN) {
                    $params['where']['uid'] = $user->id();
                }
            }
        } else {
            // for now, leaving volunteer activity public
            // this means any one can view volunteer hours with:
            // GET /api/volunteers/volunteer_hours
        }

        return parent::find($params);
    }

    ////////////////////////
    // HOOKS
    ////////////////////////

    protected function preCreateHook(&$data)
    {
        $org = new Organization(U::array_value($data, 'organization'));

        // check creator permission
        $requester = $this->app['user'];
        $role = $org->getRoleOfUser($requester);
        if ($role < ORGANIZATION_ROLE_VOLUNTEER && !$requester->isAdmin()) {
            $this->app['errors']->push(['error' => ERROR_NO_PERMISSION]);

            return false;
        }

        // volunteers cannot approve own hours
        if ($role < ORGANIZATION_ROLE_ADMIN && !$requester->isAdmin()) {
            $data['approved'] = false;
        }

        // validate number of hours
        $hours = $data['hours'] = floor($data['hours']);
        if ($hours <= 0 || $hours >= 13) {
            $this->app['errors']->push(['error' => 'invalid_num_volunteer_hours']);

            return false;
        }

        // convert day timestamp to beginning of day
        $data['timestamp'] = self::timestampToStartOfDay($data['timestamp']);

        // the timestamp on hours cannot be more than 1 day in the future
        if ($data['timestamp'] - 86400 > time()) {
            $this->app['errors']->push(['error' => 'invalid_hours_timestamp']);

            return false;
        }

        // approval link
        if (!U::array_value($data, 'approved')) {
            $data['approval_link'] = U::guid(false);
        }

        if (isset($data['tags'])) {
            self::$createTags = $data['tags'];
            if (!is_array(self::$createTags)) {
                self::$createTags = explode(' ', self::$createTags);
            }
        }

        return true;
    }

    protected function postCreateHook()
    {
        // create tags
        if (self::$createTags) {
            $tags = array_unique(self::$createTags);

            foreach ($tags as $tag) {
                $tagModel = new VolunteerHourTag();
                $tagModel->grantAllPermissions();
                $tagModel->create([
                    'tag' => $tag,
                    'hour' => $this->_id,
                    'organization' => $this->organization, ]);
            }

            self::$createTags = false;
        }

        $user = $this->relation('uid');
        $org = $this->relation('organization');

        // increment user's stats
        $user->incrementStats($this->metrics());

        // e-mail user that new hours have been added
        if ($this->approved) {
            if (!$this->canSendEmail()) {
                return;
            }

            $user->sendEmail(
                'volunteer-hours-added',
                [
                    'subject' => 'New volunteer hours added on InspireVive',
                    'orgname' => $org->name,
                    'place_name' => $this->relation('place')->name,
                    'day' => date('l, M j, Y', $this->timestamp),
                    'hours' => $this->hours,  ]);

            // update last sent time
            $volunteer = $this->volunteer();
            $volunteer->grantAllPermissions();
            $volunteer->set('last_email_sent_about_hours', time());
        }
        // seek approval/verification
        else {
            $place = $this->relation('place');

            // request verification from third party volunteer coordinator
            if ($place->canApproveHours()) {
                $this->requestThirdPartyVerification();
            }

            // notify organization of unapproved hours
            else {
                $volunteerOrg = $org->volunteerOrganization();
                // they have a volunteer organization
                if ($volunteerOrg) {
                    $volunteerOrg->grantAllPermissions();
                    $volunteerOrg->set('unapproved_hours_notify_count', $volunteerOrg->unapproved_hours_notify_count + 1);
                    $volunteerOrg->enforcePermissions();
                }
            }
        }
    }

    protected function preSetHook(&$data)
    {
        // cannot edit hours
        if (isset($data['hours']) && $data['hours'] != $this->hours) {
            return false;
        }

        if (isset($data['approved'])) {
            // email user if going from not approved to approved
            // and, update the user's volunteer hour count
            if ($data['approved'] && !$this->approved) {
                $data['approval_link'] = null;
                $this->needsApproveEmail = true;
            // cannot unapprove hours once approved
            } elseif (!$data['approved'] && $this->approved) {
                return false;
            }
        }

        if (isset($data['tags'])) {
            $this->setTags = $data['tags'];
            if (!is_array($this->setTags)) {
                $this->setTags = explode(' ', $this->setTags);
            }
        }

        // compute the delta between the stats
        $user = $this->relation('uid');
        $this->_delta = $this->metrics();

        return true;
    }

    protected function postSetHook()
    {
        $user = $this->relation('uid');

        // increment user's stats
        $metrics = $this->metrics();
        foreach ($this->_delta as $key => &$value) {
            $value = $metrics[$key] - $value;
        }
        $user->incrementStats($this->_delta);

        // update tags
        if ($this->setTags) {
            // remove existing tags
            Database::delete('VolunteerHourTags', ['hour' => $this->_id]);

            $tags = array_unique($this->setTags);

            foreach ($tags as $tag) {
                $tagModel = new VolunteerHourTag();
                $tagModel->grantAllPermissions();
                $tagModel->create([
                    'tag' => $tag,
                    'hour' => $this->_id,
                    'organization' => $this->organization, ]);
            }

            $this->setTags = false;
        }

        // email user if going from unapproved to approved
        if ($this->needsApproveEmail) {
            if (!$this->canSendEmail()) {
                return;
            }

            $org = $this->relation('organization');

            $user->sendEmail(
                'volunteer-hours-approved',
                [
                    'subject' => 'Your volunteer hours were approved on InspireVive',
                    'orgname' => $org->name,
                    'place_name' => $this->relation('place')->name,
                    'day' => date('l, M j, Y', $this->timestamp),
                    'hours' => $this->hours,  ]);

            // update last sent time
            $volunteer = $this->volunteer();
            $volunteer->grantAllPermissions();
            $volunteer->set('last_email_sent_about_hours', time());

            $this->needsApproveEmail = false;
        }
    }

    protected function preDeleteHook()
    {
        $this->_deleteUser = $this->relation('uid');

        // notify user that hours were rejected
        if ($this->canSendEmail()) {
            $org = $this->relation('organization');

            $this->_deleteEmail = [
                'subject' => 'Your volunteer hours were not approved on InspireVive',
                'orgname' => $org->name,
                'place_name' => $this->relation('place')->name,
                'day' => date('l, M j, Y', $this->timestamp),
                'hours' => $this->hours, ];
        }

        // compute the delta between the stats
        $this->_delta = $this->metrics();
        foreach ($this->_delta as &$value) {
            $value = -$value;
        }

        return true;
    }

    protected function postDeleteHook()
    {
        if ($this->_deleteEmail) {
            $this->_deleteUser->sendEmail('volunteer-hours-rejected', $this->_deleteEmail);
        }

        $this->_deleteUser->incrementStats($this->_delta);
    }

    protected function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!isset($exclude['tags'])) {
            $result['tags'] = $this->tags();
        }
    }

    ///////////////////////
    // GETTERS
    ///////////////////////

    /**
     * Returns metrics relevant to the activity, i.e. likes or retweets.
     *
     * @return array
     */
    public function metrics()
    {
        return [
            'volunteer_hours' => ($this->approved) ? $this->hours : 0, ];
    }

    /**
     * Generates an attributed message for the activity.
     *
     * @return string
     */
    public function attributedMessage()
    {
        $user = $this->relation('uid');
        $hours = $this->volunteer_hours;
        $org = $this->relation('organization');

        return [
            [
                'type' => 'string',
                'value' => $user->name().' volunteered '.$hours.$this->app['locale']->p($hours, ' hour', ' hours').
                    ' at '.$org->name.'!',
            ],
        ];
    }

    /**
     * Gets the volunteer associated with this hour.
     *
     * @return Volunteer
     */
    public function volunteer()
    {
        return new Volunteer([$this->uid, $this->organization]);
    }

    /**
     * Gets the place associated with this hour.
     *
     * @return VolunteerPlace
     */
    public function place()
    {
        return $this->relation('place');
    }

    /**
     * Gets the tags associated with this hour.
     *
     * @return array(string)
     */
    public function tags()
    {
        return (array) Database::select(
            'VolunteerHourTags',
            'tag',
            [
                'where' => [
                    'hour' => $this->_id, ],
                'fetchStyle' => 'singleColumn',
                'orderBy' => 'tag ASC',
                'groupBy' => 'tag', ]);
    }

    /**
     * Generates an approval link for the hour.
     *
     * @return string
     */
    public function approvalLink()
    {
        if ($this->approved) {
            return false;
        }

        return $this->relation('organization')->volunteerOrganization()->url().'/hours/approve/'.$this->approval_link;
    }

    /**
     * Generates a reject link for the hour.
     *
     * @return string
     */
    public function rejectLink()
    {
        if ($this->approved) {
            return false;
        }

        return $this->relation('organization')->volunteerOrganization()->url().'/hours/reject/'.$this->approval_link;
    }

    /**
     * Checks if the user can be emailed about this hour.
     *
     * @return bool
     */
    public function canSendEmail()
    {
        // TODO is this correct?
        $user = $this->relation('uid');

        if ($user->isTemporary()) {
            return false;
        }

        return true;

        if (!$this->approved) {
            return false;
        }

        $user = $this->relation('uid');

        if ($user->isTemporary()) {
            return false;
        }

        return $this->volunteer()->last_email_sent_about_hours < (time() - self::MINUTES_BETWEEN_EMAILS * 60);
    }

    /**
     * Rounds a timestamp down to the start of a day.
     *
     * @param int $ts unix timestamp
     *
     * @return int modified timestamp
     */
    public static function timestampToStartOfDay($ts)
    {
        return strtotime('midnight', $ts);
    }

    /**
     * Rounds a timestamp down to the end of a day.
     *
     * @param int $ts unix timestamp
     *
     * @return int modified timestamp
     */
    public static function timestampToEndOfDay($ts)
    {
        return strtotime('tomorrow', $ts) - 1;
    }

    ///////////////////////
    // SETTERS
    ///////////////////////

    /**
     * Requests verification from the third-party that the hour was valid
     * Only works for hours volunteered at an external place.
     *
     * @return bool
     */
    public function requestThirdPartyVerification()
    {
        $place = $this->place();

        if (!$place->canApproveHours()) {
            return false;
        }

        $o = $this->relation('organization')->toArray();
        $p = $place->toArray();
        $user = $this->relation('uid');
        $volunteerName = $user->name(true);

        $success = $this->app['mailer']->queueEmail(
            'volunteer-hours-verification-request',
            [
                'from_name' => $o['name'],
                'to' => [
                    [
                        'name' => $p['verify_name'],
                        'email' => $p['verify_email'], ], ],
                'subject' => 'Requesting your approval of volunteer hours from '.$volunteerName,
                'orgname' => $o['name'],
                'coordinator_name' => $p['verify_name'],
                'volunteer_name' => $volunteerName,
                'volunteer_url' => $user->url(),
                'place_name' => $p['name'],
                'day' => date('l, M j, Y', $this->timestamp),
                'hours' => $this->hours,
                'approval_link' => $this->approvalLink(),
                'reject_link' => $this->rejectLink(), ]);

        $this->grantAllPermissions();

        return ($success) ? $this->set(['verification_requested' => true, 'verification_requested_at' => time()]) : false;
    }
}
