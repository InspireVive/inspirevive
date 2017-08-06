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

class Volunteer extends ACLModel
{
    const ROLE_NONE = -1;
    const ROLE_AWAITING_APPROVAL = 0;
    const ROLE_VOLUNTEER = 1;
    const ROLE_ADMIN = 2;

    const STATUS_COORDINATOR = 'volunteer_coordinator';
    const STATUS_ACTIVE_VOLUNTEER = 'active_volunteer';
    const STATUS_INACTIVE_VOLUNTEER = 'inactive_volunteer';
    const STATUS_INCOMPLETE_APPLICATION = 'incomplete_application';
    const STATUS_AWAITING_APPROVAL = 'awaiting_approval';
    const STATUS_NOT_VOLUNTEER = 'not_volunteer';
    const STATUS_NOT_REGISTERED = 'not_registered';

    protected static $ids = ['uid', 'organization'];
    protected static $properties = [
        'uid' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => User::class,
        ],
        'organization' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => Organization::class,
        ],
        'application_shared' => [
            'type' => Model::TYPE_BOOLEAN,
            'default' => false,
            'admin_type' => 'checkbox',
        ],
        'active' => [
            'type' => Model::TYPE_BOOLEAN,
            'default' => true,
            'admin_hidden_property' => true,
            'admin_type' => 'checkbox',
        ],
        'approval_link' => [
            'type' => Model::TYPE_STRING,
            'null' => true,
        ],
        'role' => [
            'type' => Model::TYPE_INTEGER,
            'default' => self::ROLE_AWAITING_APPROVAL,
            'admin_type' => 'enum',
            'admin_enum' => [
                self::ROLE_NONE => 'none',
                self::ROLE_AWAITING_APPROVAL => 'awaiting_approval',
                self::ROLE_VOLUNTEER => 'volunteer',
                self::ROLE_ADMIN => 'admin', ],
        ],
        'last_email_sent_about_hours' => [
            'type' => Model::TYPE_DATE,
            'null' => true,
            'admin_hidden_property' => true,
            'admin_type' => 'datepicker',
        ],
        'metadata' => [
            'type' => Model::TYPE_OBJECT,
            'null' => true,
            'admin_type' => 'json',
        ],
    ];

    public static $autoTimestamps;

    /**
     * @var bool
     */
    private $_wasApproved;

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

        // users can edit their own volunteer model
        if ($permission == 'edit' && $this->uid == $this->getApp()['user']->id()) {
            return true;
        }

        $orgModel = $this->relation('organization');
        if (in_array($permission, ['view', 'edit', 'delete']) &&
            is_object($orgModel) &&
            $orgModel->getRoleOfUser($requester) == self::ROLE_ADMIN) {
            return true;
        }

        return $requester->isAdmin();
    }

    protected function initialize()
    {
        parent::initialize();

        self::creating([self::class, 'preCreateHook']);
        self::created([self::class, 'postCreateHook']);
        self::updating([self::class, 'preUpdateHook']);
        self::updated([self::class, 'postUpdateHook']);
    }

    ////////////////////////
    // HOOKS
    ////////////////////////

    static function preCreateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        $organization = $model->relation('organization');

        // In order to create volunteer models must be one of:
        //  i) admin
        //  ii) org admin
        //  ii) current user creating a volunteer model for themselves
        $uid = $model->uid;
        $currentRole = $organization->getRoleOfUser($model->getApp()['user']);
        $isAdmin = $model->getApp()['user']->isAdmin() || $currentRole == self::ROLE_ADMIN;

        if (!$isAdmin && $uid != $model->getApp()['user']->id()) {
            $model->getErrors()->add(ERROR_NO_PERMISSION);
            $event->stopPropagation();
            return;
        }

        // volunteers cannot be promoted beyond the role of the current user
        $maxLevel = ($isAdmin) ?
            self::ROLE_ADMIN :
            max(self::ROLE_AWAITING_APPROVAL, $currentRole);

        if ($model->role > $maxLevel) {
            $model->getErrors()->add(ERROR_NO_PERMISSION);
            $event->stopPropagation();
            return;
        }

        // approval link
        if ($model->role == self::ROLE_AWAITING_APPROVAL) {
            $model->approval_link = U::guid(false);
        }
    }

    static function postCreateHook(ModelEvent $event)
    {
        $model = $event->getModel();
        if ($model->role == self::ROLE_AWAITING_APPROVAL) {
            $model->emailOrganizationForApproval();
        }
    }

    static function preUpdateHook(ModelEvent $event)
    {
        $model = $event->getModel();
        $organization = $model->relation('organization');

        $currentUser = $model->getApp()['user'];
        $currentRole = $organization->getRoleOfUser($currentUser);
        $isAdmin = $currentUser->isAdmin() || $currentRole == self::ROLE_ADMIN;

        // volunteers can only be promoted if current user is admin
        $maxLevel = ($isAdmin) ? self::ROLE_ADMIN : self::ROLE_AWAITING_APPROVAL;

        if ($model->role > $maxLevel) {
            $model->getErrors()->add(ERROR_NO_PERMISSION);
            $event->stopPropagation();
            return;
        }

        // email user if going from not approved to approved
        if ($model->role >= self::ROLE_VOLUNTEER && $model->ignoreUnsaved()->role == self::ROLE_AWAITING_APPROVAL) {
            $model->approval_link = null;
            $model->_wasApproved = true;
        }
    }

    static function postUpdateHook(ModelEvent $event)
    {
        $model = $event->getModel();
        if ($model->_wasApproved) {
            $org = $model->relation('organization');

            $orgname = $org->name;

            $model->relation('uid')->sendEmail(
                'volunteer-application-approved',
                [
                    'subject' => 'Your request to join '.$orgname.' was approved on InspireVive',
                    'orgname' => $orgname,
                    'volunteerHubUrl' => $org->url(), ]);

            $model->_wasApproved = false;
        }
    }

    protected function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!isset($exclude['name'])) {
            $result['name'] = $this->name();
        }

        if (!isset($exclude['status'])) {
            $result['status'] = $this->status();
        }

        if (isset($include['volunteer_application'])) {
            $user = $this->relation('uid');
            $result['volunteer_application'] = false;
            if ($user->hasCompletedVolunteerApplication() && $this->application_shared) {
                $result['volunteer_application'] = $user->volunteerApplication()->toArray();
            }
        }
    }

    ////////////////////////
    // GETTERS
    ////////////////////////

    /**
     * Gets the name of the volunteer.
     *
     * @param bool $full
     *
     * @return string
     */
    public function name($full = true)
    {
        return $this->relation('uid')->name($full);
    }

    /**
     * Gets the status of the volunteer.
     *
     * @return string
     */
    public function status()
    {
        $role = $this->role;

        if ($role == self::ROLE_ADMIN) {
            return self::STATUS_COORDINATOR;
        }

        if ($role == self::ROLE_VOLUNTEER) {
            $user = $this->relation('uid');
            if ($user->isTemporary()) {
                return self::STATUS_NOT_REGISTERED;
            } else if ($user->hasCompletedVolunteerApplication()) {
                return ($this->active) ? self::STATUS_ACTIVE_VOLUNTEER : self::STATUS_INACTIVE_VOLUNTEER;
            } else {
                return self::STATUS_INCOMPLETE_APPLICATION;
            }
        }

        if ($role == self::ROLE_AWAITING_APPROVAL) {
            return self::STATUS_AWAITING_APPROVAL;
        }

        return self::STATUS_NOT_VOLUNTEER;
    }

    /**
     * Generates an approval link for the hour.
     *
     * @return string
     */
    public function approvalLink()
    {
        if ($this->role != self::ROLE_AWAITING_APPROVAL) {
            return false;
        }

        return $this->relation('organization')->url().'/volunteers/approve/'.$this->approval_link;
    }

    /**
     * Generates a reject link for the hour.
     *
     * @return string
     */
    public function rejectLink()
    {
        if ($this->role != self::ROLE_AWAITING_APPROVAL) {
            return false;
        }

        return $this->relation('organization')->url().'/volunteers/reject/'.$this->approval_link;
    }

    /**
     * Notifies the organization via email
     * for approval of the volunteer.
     *
     * @return bool
     */
    public function emailOrganizationForApproval()
    {
        $org = $this->relation('organization');
        $user = $this->relation('uid');

        $orgName = $org->name;

        $info = [
            'username' => $user->username,
            'volunteer_email' => $user->email,
            'subject' => "New volunteer requested to join $orgName on InspireVive",
            'orgname' => $orgName,
            'approval_link' => $this->approvalLink(),
            'reject_link' => $this->rejectLink(),
        ];

        if ($this->application_shared) {
            if ($application = $user->volunteerApplication()) {
                $info = array_replace($info, $application->toArray());
                $info['full_name'] = $application->fullName();
            }
        }

        return $org->sendEmail('volunteer-approval-request', $info);
    }
}
