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
use app\users\models\User;

class VolunteerApplication extends Model
{
    public static $scaffoldApi;
    public static $autoTimestamps;

    public static $properties = [
        'uid' => [
            'type' => 'number',
            'relation' => 'app\users\models\User',
            'required' => true,
            'admin_hidden_property' => true,
        ],
        'first_name' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:2',
            'searchable' => true,
        ],
        'middle_name' => [
            'type' => 'string',
            'admin_hidden_property' => true,
            'searchable' => true,
        ],
        'last_name' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:2',
            'searchable' => true,
        ],
        'address' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:5',
            'admin_hidden_property' => true,
        ],
        'city' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:2',
            'admin_hidden_property' => true,
            'searchable' => true,
        ],
        'state' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:2',
            'admin_hidden_property' => true,
        ],
        'zip_code' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:5',
            'admin_hidden_property' => true,
        ],
        'phone' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:10',
            'admin_hidden_property' => true,
        ],
        'has_sms' => [
            'type' => 'boolean',
            'default' => false,
            'validate' => 'boolean',
            'admin_hidden_property' => true,
        ],
        'alternate_phone' => [
            'type' => 'string',
            'admin_hidden_property' => true,
        ],
        'birth_date' => [
            'type' => 'date',
            'required' => true,
            'admin_hidden_property' => true,
        ],
        'first_time_volunteer' => [
            'type' => 'boolean',
            'default' => false,
            'validate' => 'boolean',
            'admin_hidden_property' => true,
        ],
    ];

    public static $defaults = [
        'address' => 'unknown',
        'city' => 'Unknown',
        'state' => 'NA',
        'zip_code' => '00000',
        'phone' => '000-000-0000',
        'birth_date' => 0,
    ];

    public static function idProperty()
    {
        return 'uid';
    }

    protected function hasPermission($permission, Model $requester)
    {
        if ($permission == 'create') {
            return $requester->isLoggedIn();
        }

        if ($requester->id() == $this->uid) {
            return true;
        }

        return $requester->isAdmin();
    }

    public function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!in_array('age', $exclude)) {
            $result['age'] = $this->age();
        }
    }

    /**
     * Gets the user's full name.
     *
     * @param bool $middle whether or not to include middle name
     *
     * @return string
     */
    public function fullName()
    {
        $names = (array) $this->get(['first_name', 'middle_name', 'last_name']);

        if (isset($names['middle_name']) && strlen($names['middle_name']) > 0) {
            $names['middle_name'] = strtoupper(substr($names['middle_name'], 0, 1)).'.';
        }

        return implode(' ', $names);
    }

    /**
     * Calculates the age based on the application's date of birth.
     *
     * @return int
     */
    public function age()
    {
        $date = new \DateTime();
        $date->setTimestamp($this->birth_date);
        $diff = $date->diff(new \DateTime());

        return $diff->y;
    }
}
