<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use infuse\Database;
use app\users\models\User;
use app\volunteers\models\VolunteerApplication;

class VolunteerApplicationTest extends PHPUnit_Framework_TestCase
{
    public static $app;

    public static function tearDownAfterClass()
    {
        if (self::$app) {
            self::$app->grantAllPermissions();
            self::$app->delete();
        }
    }

    public function testPermissions()
    {
        $user = new User(100, true);

        $application = new VolunteerApplication();
        $this->assertTrue($application->can('create', $user));

        $this->assertFalse($application->can('edit', $user));

        $application = new VolunteerApplication(100);
        $this->assertTrue($application->can('edit', $user));
    }

    public function testCreate()
    {
        Database::delete('VolunteerApplications', ['uid' => -2]);

        self::$app = new VolunteerApplication();
        self::$app->grantAllPermissions();
        $this->assertTrue(self::$app->create([
            'uid' => -2,
            'first_name' => 'Test',
            'middle_name' => 'meh',
            'last_name' => 'User',
            'address' => 'abc st',
            'city' => 'Tulsa',
            'state' => 'OK',
            'zip_code' => '74104',
            'phone' => '1234567890',
            'alternate_phone' => '1234567899',
            'birth_date' => strtotime('21 years ago'),
       ]));
    }

    /**
     * @depends testCreate
     */
    public function testFullName()
    {
        $this->assertEquals('Test M. User', self::$app->fullName());
    }

    /**
     * @depends testCreate
     */
    public function testUserName()
    {
        $user = new User(-2);
        $this->assertEquals('Test M. User', $user->name(true));
    }

    /**
     * @depends testCreate
     */
    public function testAge()
    {
        $this->assertEquals(21, self::$app->age());
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        self::$app->grantAllPermissions();
        $this->assertTrue(self::$app->delete());
    }
}
