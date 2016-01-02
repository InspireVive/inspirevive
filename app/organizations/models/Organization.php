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
use app\users\models\User;
use app\volunteers\models\Volunteer;

class Organization extends Model
{
    public static $autoTimestamps;
    public static $scaffoldApi;

    public static $properties = [
        'volunteer_organization' => [
            'type' => 'number',
            'relation' => 'app\volunteers\models\VolunteerOrganization',
            'null' => true,
        ],
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
            'VolunteerOrganizations',
            'VolunteerHours',
            'VolunteerPlaces', ];

        foreach ($nuke as $tablename) {
            Database::delete(
                $tablename,
                [
                    'organization' => $this->_id, ]);
        }
    }

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
     * Checks if the organization uses InspireVive for Organizations.
     *
     * @return bool
     */
    public function hasVolunteerOrganization()
    {
        return $this->volunteer_organization > 0;
    }

    /**
     * Attempts to get the volunteer organization.
     *
     * @return VolunteerOrganization|false
     */
    public function volunteerOrganization()
    {
        if ($this->hasVolunteerOrganization()) {
            return $this->relation('volunteer_organization');
        }

        return false;
    }
}
