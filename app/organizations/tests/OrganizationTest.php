<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use app\organizations\models\Organization;
use app\users\models\User;
use app\volunteers\models\VolunteerOrganization;
use app\volunteers\models\Volunteer;

class OrganizationTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $org2;

    public static function tearDownAfterClass()
    {
        $delete = [
            self::$org,
            self::$org2,
        ];

        foreach ($delete as $m) {
            if ($m) {
                $m->grantAllPermissions();
                $m->delete();
            }
        }
    }

    public function testPermissions()
    {
        $org = new Organization();
        $this->assertFalse($org->can('view', TestBootstrap::app('user')));
    }

    public function testHasVolunteerOrganization()
    {
        $org = new Organization();
        $org->volunteer_organization = 100;

        $this->assertTrue($org->hasVolunteerOrganization());

        $org->volunteer_organization = null;
        $this->assertFalse($org->hasVolunteerOrganization());
    }

    public function testVolunteerOrganization()
    {
        $org = new Organization();
        $org->volunteer_organization = 100;

        $volunteerOrg = $org->volunteerOrganization();
        $this->assertInstanceOf('app\volunteers\models\VolunteerOrganization', $volunteerOrg);
        $this->assertEquals(100, $volunteerOrg->id());

        $org->volunteer_organization = null;
        $this->assertFalse($org->volunteerOrganization());
    }

    public function testCreate()
    {
        $uid = TestBootstrap::app('user')->id();

        self::$org = new Organization();
        self::$org->grantAllPermissions();
        $this->assertTrue(self::$org->create([
            'name' => 'Test',
            'email' => 'test@example.com', ]));

        $this->assertEquals('test', self::$org->slug);

        self::$org2 = new Organization();
        self::$org2->grantAllPermissions();
        $this->assertTrue(self::$org2->create([
            'name' => 'Test 2',
            'email' => 'test2@example.com', ]));

        $this->assertEquals('test-2', self::$org2->slug);

        TestBootstrap::app('user')->enableSU();
        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => -2,
            'organization' => self::$org->id(), ]));
        TestBootstrap::app('user')->disableSU();

        TestBootstrap::app('user')->enableSU();
        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => $uid,
            'organization' => self::$org2->id(),
            'role' => Volunteer::ROLE_ADMIN, ]));
        TestBootstrap::app('user')->disableSU();

        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => -3,
            'organization' => self::$org2->id(), ]));
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        self::$org->grantAllPermissions();
        $this->assertTrue(self::$org->set('name', 'Testing 1 2 3'));

        $this->assertEquals('testing-1-2-3', self::$org->slug);
    }

    /**
     * @depends testCreate
     */
    public function testGetRoleOfUser()
    {
        $this->assertEquals(Volunteer::ROLE_NONE, self::$org->getRoleOfUser(new User(-1)));

        $this->assertEquals(Volunteer::ROLE_ADMIN, self::$org2->getRoleOfUser(TestBootstrap::app('user')));
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        self::$org2->grantAllPermissions();
        $this->assertTrue(self::$org2->delete());
    }
}
