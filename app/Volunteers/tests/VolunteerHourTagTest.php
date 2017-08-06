<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Organizations\Models\Organization;
use App\Users\Models\User;
use App\Volunteers\Models\VolunteerHourTag;
use App\Volunteers\Models\VolunteerHour;
use App\Volunteers\Models\Volunteer;
use Infuse\Test;

class VolunteerHourTagTest extends PHPUnit_Framework_TestCase
{
    static $org;
    static $hour;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->name = 'Test Org';
        self::$org->email = 'test@example.com';
        self::$org->username = 'test'.time();
        self::$org->saveOrFail();

        $uid = Test::$app['user']->id();
        Test::$app['user']->promoteToSuperUser();
        $volunteer = new Volunteer();
        $volunteer->organization = self::$org->id();
        $volunteer->uid = $uid;
        $volunteer->role = Volunteer::ROLE_ADMIN;
        $volunteer->saveOrFail();
        Test::$app['user']->demoteToNormalUser();

        self::$hour = new VolunteerHour;
        self::$hour->uid = $uid;
        self::$hour->organization = self::$org->id();
        self::$hour->timestamp = time();
        self::$hour->hours = 5;
        self::$hour->saveOrFail();
    }

    public static function tearDownAfterClass()
    {
        self::$org->grantAllPermissions()->delete();
    }

    function testCreate()
    {
        $tag = new VolunteerHourTag;
        $tag->organization = self::$org->id();
        $tag->hour = self::$hour->id();
        $this->assertTrue($tag->save());
    }
}
