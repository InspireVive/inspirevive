<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use app\users\models\User;
use app\volunteers\models\VolunteerHourTag;

class VolunteerHourTagTest extends PHPUnit_Framework_TestCase
{
    public function testPermissions()
    {
        $tag = new VolunteerHourTag();
        $user = new User(-1);
        $this->assertFalse($tag->can('create', $user));
        $this->assertFalse($tag->can('edit', $user));
        $this->assertFalse($tag->can('delete', $user));
        $this->assertFalse($tag->can('view', $user));
    }
}
