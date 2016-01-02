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
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerOrganization;
use app\volunteers\models\VolunteerApplication;

class VolunteerTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $volunteerOrg;
    public static $volunteer;
    public static $volunteer2;
    public static $app;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test Org',
            'email' => 'test@example.com',
            'username' => 'test'.time()
        ]);

        TestBootstrap::app('user')->enableSU();
        self::$volunteerOrg = new VolunteerOrganization();
        self::$volunteerOrg->create([
            'organization' => self::$org->id()
        ]);
        TestBootstrap::app('user')->disableSU();
    }

    protected function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$volunteerOrg->id());
    }

    public static function tearDownAfterClass()
    {
        foreach ([self::$volunteer, self::$volunteer2, self::$app] as $m) {
            if ($m) {
                $m->grantAllPermissions();
                $m->delete();
            }
        }
    }

    public function testPermissions()
    {
        $volunteer = new Volunteer();
        $user = new User();
        $this->assertFalse($volunteer->can('edit', $user));
        $this->assertFalse($volunteer->can('view', $user));
        $this->assertFalse($volunteer->can('delete', $user));
    }

    public function testCannotCreateOtherVolunteer()
    {
        $volunteer = new Volunteer();
        $this->assertFalse($volunteer->create([
            'uid' => -100,
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_ADMIN, ]));
    }

    public function testCannotCreateBeyondCurrentRole()
    {
        $volunteer = new Volunteer();
        $this->assertFalse($volunteer->create([
            'uid' => TestBootstrap::app('user')->id(),
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_ADMIN, ]));
    }

    public function testName()
    {
        $this->createVolunteerApp();
        $volunteer = new Volunteer();
        $volunteer->uid = TestBootstrap::app('user')->id();
        $volunteer->application_shared = true;
        $this->assertEquals('Test M. User', $volunteer->name(true));
    }

    public function testStatus()
    {
        $this->createVolunteerApp();
        $volunteer = new Volunteer();
        $volunteer->uid = TestBootstrap::app('user')->id();

        $volunteer->role = Volunteer::ROLE_ADMIN;
        $this->assertEquals('volunteer_coordinator', $volunteer->status());

        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $volunteer->active = false;
        $this->assertEquals('inactive_volunteer', $volunteer->status());

        $volunteer->active = true;
        $this->assertEquals('active_volunteer', $volunteer->status());

        self::$app->delete();
        $this->assertEquals('incomplete_application', $volunteer->status());

        $volunteer->uid = -3;
        $this->assertEquals('not_registered', $volunteer->status());

        $volunteer->role = Volunteer::ROLE_AWAITING_APPROVAL;
        $this->assertEquals('awaiting_approval', $volunteer->status());

        $volunteer->role = Volunteer::ROLE_NONE;
        $this->assertEquals('not_volunteer', $volunteer->status());
    }

    public function testApprovalLink()
    {
        $volunteer = new Volunteer();
        $volunteer->organization = self::$org->id();
        $volunteer->approval_link = 'test';
        $expected = self::$volunteerOrg->url().'/volunteers/approve/test';

        $this->assertEquals($expected, $volunteer->approvalLink());
    }

    public function testRejectLink()
    {
        $volunteer = new Volunteer();
        $volunteer->organization = self::$org->id();
        $volunteer->approval_link = 'test';
        $expected = self::$volunteerOrg->url().'/volunteers/reject/test';

        $this->assertEquals($expected, $volunteer->rejectLink());
    }

    public function testCreate()
    {
        self::$volunteer = new Volunteer();
        self::$volunteer->grantAllPermissions();
        $this->assertTrue(self::$volunteer->create([
            'uid' => TestBootstrap::app('user')->id(),
            'organization' => self::$org->id(),
            'application_shared' => true, ]));

        TestBootstrap::app('user')->enableSU();
        self::$volunteer2 = new Volunteer();
        self::$volunteer2->grantAllPermissions();
        $this->assertTrue(self::$volunteer2->create([
            'uid' => -3,
            'organization' => self::$org->id(), ]));
        TestBootstrap::app('user')->disableSU();
    }

    public function testToArray()
    {
        $expected = [
            'uid' => TestBootstrap::app('user')->id(),
            'organization' => self::$org->id(),
            'application_shared' => true,
            'active' => true,
            'role' => Volunteer::ROLE_AWAITING_APPROVAL,
            'last_email_sent_about_hours' => null,
            'metadata' => null,
            'name' => TestBootstrap::app('user')->name(),
            'status' => 'awaiting_approval',
       ];

        $this->assertEquals($expected, self::$volunteer->toArray(['updated_at', 'created_at', 'approval_link']));
    }

    /**
     * @depends testCreate
     */
    public function testEmailOrganizationForApproval()
    {
        $this->createVolunteerApp();
        $this->assertTrue(self::$volunteer->emailOrganizationForApproval());
    }

    /**
     * @depends testCreate
     */
    public function testApproveVolunteer()
    {
        TestBootstrap::app('user')->enableSU();
        $this->assertTrue(self::$volunteer->set([
            'role' => Volunteer::ROLE_VOLUNTEER,
            'approval_link' => '', ]));
    }

    /**
     * @depends testCreate
     */
    public function testCannotPromoteBeyondCurrentRole()
    {
        self::$volunteer->enforcePermissions();
        $this->assertFalse(self::$volunteer->set('role', Volunteer::ROLE_VOLUNTEER));
    }

    /**
     * @depends testCreate
     */
    public function testFindOnlyUser()
    {
        // should not be able to see anything without supplying organization
        $volunteers = Volunteer::find()['models'];

        $this->assertCount(1, $volunteers);
        $this->assertEquals(TestBootstrap::app('user')->id(), $volunteers[0]->uid);
    }

    /**
     * @depends testCreate
     */
    public function testFind()
    {
        // try with the organization supplied
        $volunteers = Volunteer::find([
            'where' => [
                'organization' => self::$org->id(), ],
            'sort' => 'id ASC', ])['models'];

        $this->assertCount(2, $volunteers);

        // look for our 3 known customers
        $find = [self::$volunteer->id(), self::$volunteer2->id()];
        foreach ($volunteers as $m) {
            if (($key = array_search($m->uid, $find)) !== false) {
                unset($find[$key]);
            }
        }
        $this->assertCount(0, $find);
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        self::$volunteer->grantAllPermissions();
        $this->assertTrue(self::$volunteer->delete());
    }

    private function createVolunteerApp()
    {
        $uid = TestBootstrap::app('user')->id();
        if ((new VolunteerApplication($uid))->exists()) {
            return;
        }

        self::$app = new VolunteerApplication();
        self::$app->grantAllPermissions();
        self::$app->create([
            'uid' => $uid,
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
       ]);
    }
}
