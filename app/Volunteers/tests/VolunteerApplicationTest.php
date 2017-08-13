<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Users\Models\User;
use App\Volunteers\Models\VolunteerApplication;
use Infuse\Test;

class VolunteerApplicationTest extends PHPUnit_Framework_TestCase
{
    public static $app;

    static function setUpBeforeClass()
    {
        Test::$app['database']->getDefault()
            ->delete('VolunteerApplications')
            ->where('uid', Test::$app['user']->id())
            ->execute();
    }

    public function testPermissions()
    {
        $user = new User(100);
        $user->markSignedIn();

        $application = new VolunteerApplication();
        $this->assertTrue($application->can('create', $user));

        $this->assertFalse($application->can('edit', $user));

        $application = new VolunteerApplication(100);
        $this->assertTrue($application->can('edit', $user));
    }

    public function testCreate()
    {
        self::$app = new VolunteerApplication();
        self::$app->grantAllPermissions();
        $this->assertTrue(self::$app->create([
            'uid' => Test::$app['user']->id(),
            'first_name' => 'Test',
            'middle_name' => 'meh',
            'last_name' => 'Person',
            'address' => 'abc st',
            'city' => 'Tulsa',
            'state' => 'OK',
            'zip_code' => '74104',
            'phone' => '1234567890',
            'alternate_phone' => '1234567899',
            'birth_date' => strtotime('21 years ago'),
        ]));
        $this->assertEquals('Test Person', Test::$app['user']->refresh()->full_name);
    }

    /**
     * @depends testCreate
     */
    public function testFullName()
    {
        $this->assertEquals('Test M. Person', self::$app->fullName());
    }

    /**
     * @depends testCreate
     */
    public function testUserFullName()
    {
        $this->assertEquals('Test Person', Test::$app['user']->name(true));
    }

    /**
     * @depends testCreate
     */
    function testEdit()
    {
        self::$app->first_name = 'Changed';
        $this->assertTrue(self::$app->save());
        $this->assertEquals('Changed Person', Test::$app['user']->refresh()->full_name);
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
