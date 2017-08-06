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
use Pulsar\Model;

class VolunteerHourTag extends Model
{
    protected static $ids = ['tag', 'hour'];
    public static $properties = [
        'tag' => [
            'type' => Model::TYPE_INTEGER,
            'validate' => 'alpha_dash:1',
        ],
        'hour' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => VolunteerHour::class,
        ],
        'organization' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => Organization::class,
            'required' => true,
        ],
    ];

    protected function setTagValue($tag)
    {
        return strtolower($tag);
    }
}
