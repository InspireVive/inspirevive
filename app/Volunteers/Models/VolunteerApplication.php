<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Volunteers\Models;

use App\Users\Models\User;
use Pulsar\ACLModel;
use Pulsar\Model;
use Pulsar\ModelEvent;

class VolunteerApplication extends ACLModel
{
    protected static $ids = ['uid'];
    protected static $properties = [
        'uid' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => User::class,
            'required' => true,
            'admin_hidden_property' => true,
        ],
        'first_name' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:2',
            'searchable' => true,
        ],
        'middle_name' => [
            'type' => Model::TYPE_STRING,
            'admin_hidden_property' => true,
            'searchable' => true,
        ],
        'last_name' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:2',
            'searchable' => true,
        ],
        'address' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:5',
            'admin_hidden_property' => true,
        ],
        'city' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:2',
            'admin_hidden_property' => true,
            'searchable' => true,
        ],
        'state' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:2',
            'admin_hidden_property' => true,
        ],
        'zip_code' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:5',
            'admin_hidden_property' => true,
        ],
        'phone' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:10',
            'admin_hidden_property' => true,
        ],
        'alternate_phone' => [
            'type' => Model::TYPE_STRING,
            'admin_hidden_property' => true,
        ],
        'birth_date' => [
            'type' => Model::TYPE_DATE,
            'required' => true,
            'admin_hidden_property' => true,
        ],
    ];

    public static $autoTimestamps;

    public static $defaults = [
        'address' => 'unknown',
        'city' => 'Unknown',
        'state' => 'NA',
        'zip_code' => '00000',
        'phone' => '000-000-0000',
        'birth_date' => 0,
    ];

    protected function hasPermission($permission, Model $requester)
    {
        if ($permission == 'create') {
            return $requester->isSignedIn();
        }

        if ($requester->id() == $this->uid) {
            return true;
        }

        return $requester->isAdmin();
    }

    protected function initialize()
    {
        parent::initialize();

        self::saved([self::class, 'writeFullName']);
    }

    public function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!in_array('age', $exclude)) {
            $result['age'] = $this->age();
        }
    }

    static function writeFullName(ModelEvent $event)
    {
        $model = $event->getModel();
        $user = $model->relation('uid');
        $user->full_name = $model->first_name.' '.$model->last_name;
        $user->saveOrFail();
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
