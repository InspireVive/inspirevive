<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Volunteers\Models;

use App\Organizations\Models\Organization;
use App\Users\Models\User;
use Infuse\Application;
use Infuse\Utility as U;
use Pulsar\ACLModel;
use Pulsar\Model;
use Pulsar\ModelEvent;

class VolunteerHour extends ACLModel
{
    public static $properties = [
        'uid' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => User::class,
            'required' => true,
            'mutable' => Model::MUTABLE_CREATE_ONLY
        ],
        'organization' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => Organization::class,
            'required' => true,
            'mutable' => Model::MUTABLE_CREATE_ONLY
        ],
        'timestamp' => [
            'type' => Model::TYPE_DATE,
            'required' => true,
            'admin_type' => 'datepicker',
        ],
        'hours' => [
            'type' => Model::TYPE_INTEGER,
            'required' => true,
            'validate' => 'numeric',
            'mutable' => Model::MUTABLE_CREATE_ONLY,
        ],
        'place' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => VolunteerPlace::class,
            'null' => true,
            'admin_hidden_property' => true,
        ],
        'approval_link' => [
            'type' => Model::TYPE_STRING,
            'null' => true,
        ],
        'approved' => [
            'type' => Model::TYPE_BOOLEAN,
            'default' => false,
            'admin_hidden_property' => true,
        ],
        'verification_requested' => [
            'type' => Model::TYPE_BOOLEAN,
            'default' => false,
            'admin_hidden_property' => true,
        ],
        'verification_requested_at' => [
            'type' => Model::TYPE_DATE,
            'null' => true,
            'admin_type' => 'datepicker',
            'admin_hidden_property' => true,
        ],
    ];

    public static $autoTimestamps;

    /**
     * @var array
     */
    private $_saveTags;

    /**
     * @var bool
     */
    private $_wasApproved;

    /**
     * @var Volunteer
     */
    private $_volunteer;

    protected function hasPermission($permission, Model $requester)
    {
        // find permission verified in find()
        if ($permission == 'find' && $requester->isSignedIn()) {
            return true;
        }

        // create permission verified in preCreateHook()
        if ($permission == 'create' && $requester->isSignedIn()) {
            return true;
        }

        // allow user's to edit their own hours (for the purpose of changing balance)
        if ($permission == 'edit' && $requester->id() == $this->relation('uid')->id()) {
            return true;
        }

        $orgModel = $this->relation('organization');

        if (in_array($permission, ['view', 'edit', 'delete']) &&
            $orgModel &&
            $orgModel->getRoleOfUser($requester) == Volunteer::ROLE_ADMIN) {
            return true;
        }

        return $requester->isAdmin();
    }

    protected function initialize()
    {
        parent::initialize();

        self::creating([self::class, 'validateHours']);
        self::creating([self::class, 'verifyIsVolunteer']);
        self::creating([self::class, 'preCreateHook']);
        self::created([self::class, 'postCreateHook']);
        self::updating([self::class, 'preUpdateHook']);
        self::updated([self::class, 'postUpdateHook']);
        self::deleted([self::class, 'postDeleteHook']);
    }

    ////////////////////////
    // HOOKS
    ////////////////////////

    static function validateHours(ModelEvent $event)
    {
        $model = $event->getModel();

        // validate number of hours
        $hours = $model->hours;
        if ($hours <= 0 || $hours >= 13) {
            $model->getErrors()->add('invalid_num_volunteer_hours');

            $event->stopPropagation();
            return;
        }

        // the timestamp on hours cannot be more than 1 day in the future
        if ($model->timestamp - 86400 > time()) {
            $model->getErrors()->add('invalid_hours_timestamp');

            $event->stopPropagation();
            return;
        }
    }

    static function verifyIsVolunteer(ModelEvent $event)
    {
        $model = $event->getModel();

        // check if the user exists
        $user = $model->relation('uid');
        if (!$user) {
            $model->getErrors()->add('No such user: '.$model->uid);
            $event->stopPropagation();
            return;
        }

        // check if user is a volunteer of the organization
        $volunteer = $model->volunteer();
        if (!$volunteer) {
            $model->getErrors()->add('Cannot create this hour entry because the user is not a registered volunteer of this organization.');
            $event->stopPropagation();
            return;
        }

        // check if the user is an approved volunteer
        if ($volunteer->role < Volunteer::ROLE_VOLUNTEER) {
            $model->getErrors()->add('This volunteer must be approved before hours can be recorded.');

            $event->stopPropagation();
            return;
        }
    }

    static function preCreateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        $org = $model->relation('organization');

        $requester = $model->getApp()['user'];
        $role = $org->getRoleOfUser($requester);
        if ($role < Volunteer::ROLE_VOLUNTEER && !$requester->isAdmin()) {
            // must be a volunteer to report hours to this organizatoin
            $model->getErrors()->add(ERROR_NO_PERMISSION);

            $event->stopPropagation();
            return;
        } else if ($model->approved && $role < Volunteer::ROLE_ADMIN && !$requester->isAdmin()) {
            // non-admin volunteers cannot approve hours
            $model->getErrors()->add('Must be an administrator to approve hours.');

            $event->stopPropagation();
            return;
        }

        // approval link
        if (!$model->approved) {
            $model->approval_link = U::guid(false);
        }

        if (isset($model->tags)) {
            $model->_saveTags = $model->tags;
            unset($model->tags);
            if (!is_array($model->_saveTags)) {
                $model->_saveTags = explode(' ', $model->_saveTags);
            }
        }
    }

    static function postCreateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        // create tags
        if ($model->_saveTags) {
            $tags = array_filter(array_unique($model->_saveTags));

            foreach ($tags as $tag) {
                $tagModel = new VolunteerHourTag();
                $tagModel->tag = $tag;
                $tagModel->hour = $model->id();
                $tagModel->organization = $model->organization;
                $tagModel->saveOrFail();
            }

            $model->_saveTags = false;
        }

        $user = $model->relation('uid');
        $org = $model->relation('organization');

        // increment user's stats
        $user->incrementStats($model->metrics());

        $place = $model->place();

        // email user that new hours have been added
        if ($model->approved) {
            if (!$model->canSendEmail()) {
                return;
            }

            $user->sendEmail(
                'volunteer-hours-added',
                [
                    'subject' => 'New volunteer hours added on InspireVive',
                    'orgname' => $org->name,
                    'place_name' => $place ? $place->name : false,
                    'day' => date('l, M j, Y', $model->timestamp),
                    'hours' => $model->hours,  ]);

            // update last sent time
            $volunteer = $model->volunteer();
            $volunteer->last_email_sent_about_hours = time();
            $volunteer->grantAllPermissions()->saveOrFail();
        } else if ($place && $place->canApproveHours()) {
            // seek approval/verification
            // request verification from third party volunteer coordinator
            $model->requestThirdPartyVerification();
        } else {
            // notify organization of unapproved hours
            $org->unapproved_hours_notify_count++;
            $org->grantAllPermissions()->saveOrFail();
            $org->enforcePermissions();
        }
    }

    static function preUpdateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        if ($model->approved && !$model->ignoreUnsaved()->approved) {
            // email user if going from not approved to approved
            // and, update the user's volunteer hour count
            $model->approval_link = null;
            $model->_wasApproved = true;
            // cannot unapprove hours once approved
        } elseif (!$model->approved && $model->ignoreUnsaved()->approved) {
            $event->stopPropagation();
            $model->getErrors()->add('Hours cannot go from approved to unapproved');
            return;
        }

        if ($model->tags) {
            $model->_saveTags = $model->tags;
            unset($model->tags);
            if (!is_array($model->_saveTags)) {
                $model->_saveTags = explode(' ', $model->_saveTags);
            }
        }
    }

    static function postUpdateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        $user = $model->relation('uid');

        // increment user's stats after approval
        if ($model->_wasApproved) {
            $user->incrementStats($model->metrics());
        }

        // update tags
        if ($model->_saveTags) {
            // remove existing tags
            $db = $model->getApp()['database']->getDefault();
            $db->delete('VolunteerHourTags')
               ->where('hour', $model->id())
               ->execute();

            $tags = array_filter(array_unique($model->_saveTags));

            foreach ($tags as $tag) {
                $tagModel = new VolunteerHourTag();
                $tagModel->tag = $tag;
                $tagModel->hour = $model->id();
                $tagModel->organization = $model->organization;
                $tagModel->saveOrFail();
            }

            $model->_saveTags = false;
        }

        // email user if going from unapproved to approved
        if ($model->_wasApproved && $model->canSendEmail()) {
            $org = $model->relation('organization');
            $place = $model->place();

            $user->sendEmail(
                'volunteer-hours-approved',
                [
                    'subject' => 'Your volunteer hours were approved on InspireVive',
                    'orgname' => $org->name,
                    'place_name' => $place ? $place->name : false,
                    'day' => date('l, M j, Y', $model->timestamp),
                    'hours' => $model->hours,  ]);

            // update last sent time
            $volunteer = $model->volunteer();
            $volunteer->grantAllPermissions();
            $volunteer->last_email_sent_about_hours = time();
            $volunteer->saveOrFail();
        }

        $model->_wasApproved = false;
    }

    static function postDeleteHook(ModelEvent $event)
    {
        $model = $event->getModel();

        $user = $model->relation('uid');

        // decrease stats
        $delta = $model->metrics();
        foreach ($delta as &$value) {
            $value = -$value;
        }
        $user->incrementStats($delta);

        // send an email notification
        if ($model->canSendEmail()) {
            $org = $model->relation('organization');
            $place = $model->place();
            $params = [
                'subject' => 'Your volunteer hours were not approved on InspireVive',
                'orgname' => $org->name,
                'place_name' => $place ? $place->name : false,
                'day' => date('l, M j, Y', $model->timestamp),
                'hours' => $model->hours,
            ];
            $user->sendEmail('volunteer-hours-rejected', $params);
        }
    }

    protected function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!isset($exclude['tags'])) {
            $result['tags'] = $this->tags();
        }
    }

    ///////////////////////
    // Mutators
    ///////////////////////

    protected function setHoursValue($hours)
    {
        return floor($hours);
    }

    protected function setTimestampValue($timestamp)
    {
        // convert day timestamp to beginning of day
        return self::timestampToStartOfDay($timestamp);
    }

    ///////////////////////
    // GETTERS
    ///////////////////////

    /**
     * Returns metrics generated from this activity.
     *
     * @return array
     */
    public function metrics()
    {
        return [
            'volunteer_hours' => ($this->approved) ? $this->hours : 0,
        ];
    }

    /**
     * Generates an attributed message for the activity.
     *
     * @return array
     */
    public function attributedMessage()
    {
        $user = $this->relation('uid');
        $hours = $this->volunteer_hours;
        $org = $this->relation('organization');

        return [
            [
                'type' => 'string',
                'value' => $user->name().' volunteered '.$hours.$this->getApp()['locale']->p($hours, ' hour', ' hours').
                    ' at '.$org->name.'!',
            ],
        ];
    }

    /**
     * Gets the volunteer associated with this hour.
     *
     * @return Volunteer|null
     */
    public function volunteer()
    {
        if ($this->_volunteer) {
            return $this->_volunteer;
        }

        $this->_volunteer = Volunteer::find([$this->uid, $this->organization]);

        return $this->_volunteer;
    }

    /**
     * Gets the place associated with this hour.
     *
     * @return VolunteerPlace|null
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
        $db = $this->getApp()['database']->getDefault();

        return (array) $db->select('tag')
            ->from('VolunteerHourTags')
            ->where('hour', $this->id())
            ->orderBy('tag ASC')
            ->groupBy('tag')
            ->column();
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

        return $this->relation('organization')->url().'/hours/approve/'.$this->approval_link;
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

        return $this->relation('organization')->url().'/hours/reject/'.$this->approval_link;
    }

    /**
     * Checks if the user can be emailed about this hour.
     *
     * @return bool
     */
    public function canSendEmail()
    {
        $user = $this->relation('uid');
        if ($user->isTemporary()) {
            return false;
        }

        return true;
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
        if (!$place || !$place->canApproveHours()) {
            return false;
        }

        $o = $this->relation('organization')->toArray();
        $p = $place->toArray();
        $user = $this->relation('uid');
        $volunteerName = $user->name(true);

        $params = [
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
            'reject_link' => $this->rejectLink(),
        ];

        $sent = $this->getApp()['mailer']->queueEmail(
            'volunteer-hours-verification-request',
            $params);

        if (!$sent) {
            return false;
        }

        $this->verification_requested = true;
        $this->verification_requested_at = time();
        return $this->grantAllPermissions()->save();
    }
}
