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
use App\Volunteers\Models\Volunteer;
use App\Volunteers\Models\VolunteerApplication;
use Infuse\Queue\Message;
use Infuse\Test;

class VolunteerTest extends PHPUnit_Framework_TestCase
{
    public static $org;
    public static $volunteer;
    public static $volunteer2;
    public static $volunteerApp;
    static $user;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->name = 'Test Org';
        self::$org->email = 'test@example.com';
        self::$org->username = 'test'.time();
        self::$org->saveOrFail();

        self::$user = new User;
        self::$user->username = 'test'.rand();
        self::$user->email = 'test'.rand().'@example.com';
        self::$user->password = ['testpassword', 'testpassword'];
        self::$user->ip = '10.0.0.1';
        self::$user->about = 'bio';
        self::$user->saveOrFail();

        Test::$app['database']->getDefault()
            ->delete('Users')
            ->where('email', 'test+temporary@example.com')
            ->execute();

        Test::$app['database']->getDefault()
            ->delete('VolunteerApplications')
            ->where('uid', Test::$app['user']->id())
            ->execute();
    }

    public static function tearDownAfterClass()
    {
        self::$org->grantAllPermissions()->delete();
        self::$user->grantAllPermissions()->delete();
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
            'uid' => Test::$app['user']->id(),
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_ADMIN, ]));
    }

    public function testFullName()
    {
        $this->createVolunteerApp();
        $volunteer = new Volunteer();
        $volunteer->uid = Test::$app['user']->id();
        $volunteer->application_shared = true;
        $this->assertEquals('Test Person', $volunteer->name(true));
    }

    public function testName()
    {
        $volunteer = new Volunteer();
        $volunteer->uid = Test::$app['user']->id();
        $volunteer->application_shared = true;
        $this->assertEquals('testuser', $volunteer->name());
    }

    public function testStatusCoordinator()
    {
        $volunteer = new Volunteer();
        $volunteer->role = Volunteer::ROLE_ADMIN;
        $this->assertEquals(Volunteer::STATUS_COORDINATOR, $volunteer->status());
    }

    public function testStatusActiveVolunteer()
    {
        $volunteer = new Volunteer();
        $volunteer->uid = Test::$app['user']->id();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $volunteer->active = true;
        $this->assertEquals(Volunteer::STATUS_ACTIVE_VOLUNTEER, $volunteer->status());
    }

    public function testStatusInactiveVolunteer()
    {
        $volunteer = new Volunteer();
        $volunteer->uid = Test::$app['user']->id();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $volunteer->active = false;
        $this->assertEquals(Volunteer::STATUS_INACTIVE_VOLUNTEER, $volunteer->status());
    }

    public function testStatusIncompleteApplication()
    {
        self::$volunteerApp->delete();

        $volunteer = new Volunteer();
        $volunteer->uid = self::$user->id();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $this->assertEquals(Volunteer::STATUS_INCOMPLETE_APPLICATION, $volunteer->status());
    }

    public function testStatusNotRegistered()
    {
        $user = User::createTemporary(['email' => 'test+temporary@example.com']);

        $volunteer = new Volunteer();
        $volunteer->uid = $user->id();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $this->assertEquals(Volunteer::STATUS_NOT_REGISTERED, $volunteer->status());
    }

    public function testStatusAwaitingApproval()
    {
        $volunteer = new Volunteer();
        $volunteer->uid = self::$user->id();
        $volunteer->role = Volunteer::ROLE_AWAITING_APPROVAL;
        $this->assertEquals(Volunteer::STATUS_AWAITING_APPROVAL, $volunteer->status());
    }

    public function testStatusNotVolunteer()
    {
        $volunteer = new Volunteer();
        $volunteer->role = Volunteer::ROLE_NONE;
        $this->assertEquals(Volunteer::STATUS_NOT_VOLUNTEER, $volunteer->status());
    }

    public function testApprovalLink()
    {
        $volunteer = new Volunteer();
        $volunteer->organization = self::$org->id();
        $volunteer->approval_link = 'test';
        $expected = self::$org->url().'/volunteers/approve/test';

        $this->assertEquals($expected, $volunteer->approvalLink());
    }

    public function testRejectLink()
    {
        $volunteer = new Volunteer();
        $volunteer->organization = self::$org->id();
        $volunteer->approval_link = 'test';
        $expected = self::$org->url().'/volunteers/reject/test';

        $this->assertEquals($expected, $volunteer->rejectLink());
    }

    public function testCreate()
    {
        self::$volunteer = new Volunteer();
        self::$volunteer->grantAllPermissions();
        $this->assertTrue(self::$volunteer->create([
            'uid' => Test::$app['user']->id(),
            'organization' => self::$org->id(),
            'application_shared' => true, ]));

        Test::$app['user']->promoteToSuperUser();
        self::$volunteer2 = new Volunteer();
        self::$volunteer2->grantAllPermissions();
        $this->assertTrue(self::$volunteer2->create([
            'uid' => self::$user->id(),
            'organization' => self::$org->id(), ]));
        Test::$app['user']->demoteToNormalUser();
    }

    public function testToArray()
    {
        $expected = [
            'uid' => Test::$app['user']->id(),
            'organization' => self::$org->id(),
            'application_shared' => true,
            'active' => true,
            'role' => Volunteer::ROLE_AWAITING_APPROVAL,
            'last_email_sent_about_hours' => null,
            'metadata' => null,
            'name' => 'Test Person',
            'status' => Volunteer::STATUS_AWAITING_APPROVAL,
            'approval_link' => self::$volunteer->approval_link,
            'created_at' => self::$volunteer->created_at,
            'updated_at' => self::$volunteer->updated_at,
       ];

        $this->assertEquals($expected, self::$volunteer->toArray());
    }

    /**
     * @depends testCreate
     */
    public function testEmailOrganizationForApproval()
    {
        $this->createVolunteerApp();
        $message = self::$volunteer->emailOrganizationForApproval();
        $this->assertInstanceOf(Message::class, $message);
    }

    /**
     * @depends testCreate
     */
    public function testApproveVolunteer()
    {
        Test::$app['user']->promoteToSuperUser();
        self::$volunteer->role = Volunteer::ROLE_VOLUNTEER;
        self::$volunteer->approval_link = '';
        $this->assertTrue(self::$volunteer->save());
    }

    /**
     * @depends testCreate
     */
    public function testCannotPromoteBeyondCurrentRole()
    {
        self::$volunteer->enforcePermissions();
        self::$volunteer->role = Volunteer::ROLE_ADMIN;
        $this->assertFalse(self::$volunteer->save());
    }

    /**
     * @depends testCreate
     */
    public function testQuery()
    {
        // try with the organization supplied
        $volunteers = Volunteer::where('organization', self::$org->id())
            ->sort('uid ASC')
            ->all();

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
        self::$volunteerApp = new VolunteerApplication();
        self::$volunteerApp->grantAllPermissions();
        self::$volunteerApp->setRelation('uid', Test::$app['user']);
        self::$volunteerApp->first_name = 'Test';
        self::$volunteerApp->middle_name = 'meh';
        self::$volunteerApp->last_name = 'Person';
        self::$volunteerApp->address = 'abc st';
        self::$volunteerApp->city = 'Tulsa';
        self::$volunteerApp->state = 'OK';
        self::$volunteerApp->zip_code = '74104';
        self::$volunteerApp->phone = '1234567890';
        self::$volunteerApp->alternate_phone = '1234567899';
        self::$volunteerApp->birth_date = strtotime('21 years ago');
        self::$volunteerApp->saveOrFail();
    }
}
