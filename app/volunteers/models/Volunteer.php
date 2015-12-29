<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\volunteers\models;

use infuse\Model;
use infuse\Utility as U;
use app\organizations\models\Organization;
use app\users\models\User;

class Volunteer extends Model
{
    public static $scaffoldApi;
    public static $autoTimestamps;

    public static $properties = [
        'uid' => [
            'type' => 'number',
            'relation' => 'app\users\models\User',
        ],
        'organization' => [
            'type' => 'number',
            'relation' => 'app\organizations\models\Organization',
        ],
        'application_shared' => [
            'type' => 'boolean',
            'default' => false,
            'admin_type' => 'checkbox',
        ],
        'active' => [
            'type' => 'boolean',
            'default' => true,
            'admin_hidden_property' => true,
            'admin_type' => 'checkbox',
        ],
        'approval_link' => [
            'type' => 'string',
            'null' => true,
        ],
        'role' => [
            'type' => 'number',
            'default' => ORGANIZATION_ROLE_AWAITING_APPROVAL,
            'admin_type' => 'enum',
            'admin_enum' => [
                ORGANIZATION_ROLE_NONE => 'none',
                ORGANIZATION_ROLE_AWAITING_APPROVAL => 'awaiting_approval',
                ORGANIZATION_ROLE_VOLUNTEER => 'volunteer',
                ORGANIZATION_ROLE_ADMIN => 'admin', ],
        ],
        'last_email_sent_about_hours' => [
            'type' => 'date',
            'null' => true,
            'admin_hidden_property' => true,
            'admin_type' => 'datepicker',
        ],
        'metadata' => [
            'type' => 'json',
            'null' => true,
            'admin_type' => 'json',
        ],
    ];

    private $needsApproveEmail;

    public static function idProperty()
    {
        return ['uid', 'organization'];
    }

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

        // users can edit their own volunteer model
        if ($permission == 'edit' && $this->uid == $this->app['user']->id()) {
            return true;
        }

        $orgModel = $this->relation('organization');
        if (in_array($permission, ['view', 'edit', 'delete']) &&
            is_object($orgModel) &&
            $orgModel->getRoleOfUser($requester) == ORGANIZATION_ROLE_ADMIN) {
            return true;
        }

        return $requester->isAdmin();
    }

    public static function find(array $params = [])
    {
        $params['where'] = (array) U::array_value($params, 'where');

        $user = self::$injectedApp['user'];
        if (!$user->isAdmin()) {
            if (isset($params['where']['organization'])) {
                $org = new Organization($params['where']['organization']);

                if ($org->getRoleOfUser($user) < ORGANIZATION_ROLE_VOLUNTEER) {
                    $params['where']['uid'] = $user->id();
                }
            } else {
                $params['where']['uid'] = $user->id();
            }
        }

        return parent::find($params);
    }

    ////////////////////////
    // HOOKS
    ////////////////////////

    public function preCreateHook(&$data)
    {
        $organization = new Organization(U::array_value($data, 'organization'));

        // In order to create volunteer models must be one of:
        //  i) admin
        //  ii) org admin
        //  ii) current user creating a volunteer model for themselves
        $uid = U::array_value($data, 'uid');
        $currentRole = $organization->getRoleOfUser($this->app['user']);
        $isAdmin = $this->app['user']->isAdmin() || $currentRole == ORGANIZATION_ROLE_ADMIN;

        if (!$isAdmin && $uid != $this->app['user']->id()) {
            $this->app['errors']->push(['error' => ERROR_NO_PERMISSION]);

            return false;
        }

        // volunteers cannot be promoted beyond the role of the current user
        $maxLevel = ($isAdmin) ?
            ORGANIZATION_ROLE_ADMIN :
            max(ORGANIZATION_ROLE_AWAITING_APPROVAL, $currentRole);

        $role = U::array_value($data, 'role');
        if ($role  > $maxLevel) {
            $this->app['errors']->push(['error' => ERROR_NO_PERMISSION]);

            return false;
        }

        // approval link
        if ($role == ORGANIZATION_ROLE_AWAITING_APPROVAL) {
            $data['approval_link'] = U::guid(false);
        }

        return true;
    }

    public function postCreateHook()
    {
        if ($this->role == ORGANIZATION_ROLE_AWAITING_APPROVAL) {
            $this->emailOrganizationForApproval();
        }
    }

    public function preSetHook(&$data)
    {
        $organization = $this->relation('organization');

        $currentUser = $this->app['user'];
        $currentRole = $organization->getRoleOfUser($currentUser);
        $isAdmin = $currentUser->isAdmin() || $currentRole == ORGANIZATION_ROLE_ADMIN;

        // volunteers can only be promoted if current user is admin
        $maxLevel = ($isAdmin) ? ORGANIZATION_ROLE_ADMIN : ORGANIZATION_ROLE_AWAITING_APPROVAL;

        $role = U::array_value($data, 'role');
        if ($role > $maxLevel) {
            $this->app['errors']->push(['error' => ERROR_NO_PERMISSION]);

            return false;
        }

        // email user if going from not approved to approved
        if ($role >= ORGANIZATION_ROLE_VOLUNTEER && $this->role == ORGANIZATION_ROLE_AWAITING_APPROVAL) {
            $data['approval_link'] = null;
            $this->needsApproveEmail = true;
        }

        return true;
    }

    public function postSetHook()
    {
        if ($this->needsApproveEmail) {
            $org = $this->relation('organization');

            if (!$org->hasVolunteerOrganization()) {
                return;
            }

            $orgname = $org->name;

            $this->relation('uid')->sendEmail(
                'volunteer-application-approved',
                [
                    'subject' => 'Your request to join '.$orgname.' was approved on InspireVive',
                    'orgname' => $orgname,
                    'volunteerHubUrl' => $org->volunteerOrganization()->url(), ]);

            $this->needsApproveEmail = false;
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

        if ($role == ORGANIZATION_ROLE_ADMIN) {
            return 'volunteer_coordinator';
        } elseif ($role == ORGANIZATION_ROLE_VOLUNTEER) {
            $user = $this->relation('uid');
            if ($user->hasCompletedVolunteerApplication()) {
                return (($this->active) ? 'active' : 'inactive').'_volunteer';
            } else {
                if ($user->isTemporary() || !$user->exists()) {
                    return 'not_registered';
                } else {
                    return 'incomplete_application';
                }
            }
        } elseif ($role == ORGANIZATION_ROLE_AWAITING_APPROVAL) {
            return 'awaiting_approval';
        }

        return 'not_volunteer';
    }

    /**
     * Generates an approval link for the hour.
     *
     * @return string
     */
    public function approvalLink()
    {
        if ($this->role != ORGANIZATION_ROLE_AWAITING_APPROVAL) {
            return false;
        }

        return $this->relation('organization')->volunteerOrganization()->url().'/volunteers/approve/'.$this->approval_link;
    }

    /**
     * Generates a reject link for the hour.
     *
     * @return string
     */
    public function rejectLink()
    {
        if ($this->role != ORGANIZATION_ROLE_AWAITING_APPROVAL) {
            return false;
        }

        return $this->relation('organization')->volunteerOrganization()->url().'/volunteers/reject/'.$this->approval_link;
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

        if (!$org->hasVolunteerOrganization()) {
            return false;
        }

        $user = $this->relation('uid');

        $orgName = $org->name;

        $info = [
            'username' => $user->username,
            'volunteer_email' => $user->user_email,
            'subject' => "New volunteer requested to join $orgName on InspireVive",
            'orgname' => $orgName,
            'approval_link' => $this->approvalLink(),
            'reject_link' => $this->rejectLink(),
        ];

        $application = new VolunteerApplication($user->id());

        if ($this->application_shared && $application->exists()) {
            $info = array_replace($info, $application->toArray());
            $info['full_name'] = $application->fullName();
        }

        return $org->volunteerOrganization()->sendEmail('volunteer-approval-request', $info);
    }
}
