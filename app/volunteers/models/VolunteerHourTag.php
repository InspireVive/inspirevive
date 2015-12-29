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

class VolunteerHourTag extends Model
{
    public static $scaffoldApi;

    public static $properties = [
        'tag' => [
            'type' => 'number',
            'validate' => 'alpha_dash:1',
        ],
        'hour' => [
            'type' => 'number',
            'relation' => 'app\volunteers\models\VolunteerHour',
        ],
        'organization' => [
            'type' => 'number',
            'relation' => 'app\organizations\models\Organization',
            'required' => true,
        ],
    ];

    public static function idProperty()
    {
        return ['tag', 'hour'];
    }

    protected function hasPermission($permission, Model $requester)
    {
        return $requester->isAdmin();
    }

    public function preCreateHook(&$data)
    {
        $data['tag'] = strtolower($data['tag']);

        return true;
    }
}
