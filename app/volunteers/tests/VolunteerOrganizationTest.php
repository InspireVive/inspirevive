<?php

use infuse\Database;
use app\users\models\User;
use app\organizations\models\Organization;
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerOrganization;
use app\volunteers\models\VolunteerHour;

class VolunteerOrganizationTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $volunteerOrg;
    public static $officialOrg;
    public static $tempUser;
    public static $tempUser2;
    public static $user;
    public static $user2;
    public static $ogUserId;

    public static function setUpBeforeClass()
    {
        Database::delete('Users', ['user_email' => 'test+volunteer@example.com']);
        self::$user = new User();
        self::$user->grantAllPermissions();
        self::$user->create([
            'user_email' => 'test+volunteer@example.com',
            'username' => 'testvolunteer1',
            'user_password' => ['testpassword', 'testpassword'],
            'ip' => '10.0.0.1', ]);

        Database::delete('Users', ['user_email' => 'test+volunteer2@example.com']);
        self::$user2 = new User();
        self::$user2->grantAllPermissions();
        self::$user2->create([
            'user_email' => 'test+volunteer2@example.com',
            'username' => 'testvolunteer2',
            'user_password' => ['testpassword', 'testpassword'],
            'ip' => '10.0.0.1', ]);

        Database::delete('Users', ['user_email' => 'temporary+volunteer@example.com']);
        Database::delete('Users', ['user_email' => 'temporary+volunteer2@example.com']);

        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test Organization',
            'email' => 'test@example.com', ]);

        self::$officialOrg = new Organization();
        self::$officialOrg->grantAllPermissions();
        self::$officialOrg->create([
            'name' => 'Test Org',
            'email' => 'test@example.com', ]);

        $uid = TestBootstrap::app('user')->id();
        TestBootstrap::app('user')->enableSU();
        $volunteer = new Volunteer();
        $volunteer->create([
            'uid' => $uid,
            'organization' => self::$org->id(),
            'role' => ORGANIZATION_ROLE_ADMIN, ]);
        TestBootstrap::app('user')->disableSU();

        $volunteer = new Volunteer();
        $volunteer->create([
            'uid' => -2,
            'organization' => self::$org->id(),
            'role' => ORGANIZATION_ROLE_AWAITING_APPROVAL, ]);

        $volunteer = new Volunteer();
        $volunteer->create([
            'uid' => -3,
            'organization' => self::$org->id(),
            'role' => ORGANIZATION_ROLE_VOLUNTEER, ]);

        $volunteer = new Volunteer();
        $volunteer->create([
            'organization' => self::$org->id(),
            'uid' => -4,
            'timestamp' => time(),
            'approved' => true,
       ]);

        /* Create some hours */

        for ($i = 0; $i < 5; ++$i) {
            $hour = new VolunteerHour();
            $hour->create([
                'organization' => self::$org->id(),
                'uid' => -3,
                'hours' => 1,
                'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
                'approved' => true,
           ]);
        }

        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$org->id(),
            'uid' => -4,
            'hours' => 10,
            'timestamp' => mktime(0, 0, 0, 5, 12, 2013),
            'approved' => true,
       ]);

        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 11,
            'approved' => true, ]);

        // unapproved hours
        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'hours' => 10,
            'tags' => ['yo', 'hello', 'test'],
            'approved' => false, ]);

        // approved hours
        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'hours' => 5,
            'approved' => true,
            'tags' => ['test'], ]);

        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'hours' => 10,
            'approved' => true,
            'tags' => ['test', 'test2'], ]);

        // official, outside hours
        TestBootstrap::app('user')->enableSU();
        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$officialOrg->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 15, 2013),
            'hours' => 12,
            'approved' => true, ]);
        TestBootstrap::app('user')->disableSU();

        self::$ogUserId = $uid;
    }

    public static function tearDownAfterClass()
    {
        $delete = [
            self::$org,
            self::$officialOrg,
            self::$tempUser,
            self::$tempUser2,
            self::$user,
            self::$user2,
       ];

        foreach ($delete as $model) {
            if ($model) {
                $model->grantAllPermissions();
                $model->delete();
            }
        }
    }

    protected function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$user->id());
        $this->assertGreaterThan(0, self::$user2->id());
        $this->assertGreaterThan(0, self::$org->id());
    }

    public function assertPostConditions()
    {
        $app = TestBootstrap::app();
        if (!$app['user']->isLoggedIn()) {
            $app['user'] = new User(self::$ogUserId, true);
        }
    }

    public function testPermissions()
    {
        $volunteerOrg = new VolunteerOrganization();
        $user = TestBootstrap::app('user');

        $this->assertTrue($volunteerOrg->can('create', $user));
        $this->assertFalse($volunteerOrg->can('view', $user));
        $this->assertFalse($volunteerOrg->can('edit', $user));
        $this->assertFalse($volunteerOrg->can('admin', $user));

        $volunteerOrg = new VolunteerOrganization();
        $volunteerOrg->organization = self::$org->id();

        $this->assertTrue($volunteerOrg->can('view', $user));
        $this->assertTrue($volunteerOrg->can('edit', $user));
        $this->assertTrue($volunteerOrg->can('admin', $user));

        $volunteerOrg = new VolunteerOrganization();
        $volunteerOrg->organization = self::$org->id();
        $user = new User(-3);

        $this->assertTrue($volunteerOrg->can('view', $user));
        $this->assertFalse($volunteerOrg->can('edit', $user));
        $this->assertFalse($volunteerOrg->can('admin', $user));
    }

    public function testCreateNoPermission()
    {
        // logout
        TestBootstrap::app()['user'] = new User(GUEST, false);

        $errorStack = TestBootstrap::app('errors');
        $errorStack->clear();

        $vOrg = new VolunteerOrganization();
        $this->assertFalse($vOrg->create([
            'organization' => self::$org->id(),
            'volunteer_coordinator_email' => 'test@example.com',
            'city' => 'Tulsa', ]));

        $errors = $errorStack->errors('VolunteerOrganization.create');
        $expected = [[
            'error' => 'no_permission',
            'message' => 'You do not have permission to do that',
            'context' => 'VolunteerOrganization.create',
            'params' => [],]];
        $this->assertEquals($expected, $errors);
    }

    public function testCreate()
    {
        self::$volunteerOrg = new VolunteerOrganization();
        $this->assertTrue(self::$volunteerOrg->create([
            'organization' => self::$org->id(),
            'volunteer_coordinator_email' => 'test@example.com',
            'city' => 'Tulsa', ]));
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        $this->assertTrue(self::$volunteerOrg->set('city', 'Austin'));
    }

    /**
     * @depends testCreate
     */
    public function testToArray()
    {
        $arr = self::$volunteerOrg->toArray();

        $this->assertTrue(is_array($arr));
        $this->assertEquals(self::$org->name, $arr['name']);
    }

    public function testName()
    {
        $this->assertEquals(self::$org->name, self::$volunteerOrg->name());
    }

    public function testHourWhereParams()
    {
        $vOrg = new VolunteerOrganization();
        $vOrg->organization = 100;

        $this->assertEquals([
            'uid IN ( SELECT uid FROM Volunteers WHERE organization = 100 AND role >= 1 )',
            'organization' => 100, ], $vOrg->hourWhereParams());
        $this->assertEquals([
            'h.uid IN ( SELECT uid FROM Volunteers WHERE organization = 100 AND role >= 1 )',
            'h.organization' => 100, ], $vOrg->hourWhereParams('h'));
    }

    /**
     * @depends testCreate
     */
    public function testUrl()
    {
        $url = self::$volunteerOrg->url();

        $this->assertTrue(is_string($url));
        $this->assertGreaterThan(10, strlen($url));
    }

    /**
     * @depends testCreate
     */
    public function testManageUrl()
    {
        $url = self::$volunteerOrg->manageUrl();

        $this->assertTrue(is_string($url));
        $this->assertGreaterThan(10, strlen($url));
    }

    /**
     * @depends testCreate
     */
    public function testNumVolunteers()
    {
        $this->assertEquals(2, self::$volunteerOrg->numVolunteers());
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteered()
    {
        $start = mktime(0, 0, 0, 5, 11, 2013);
        $end = mktime(0, 0, 0, 5, 15, 2013);

        $this->assertEquals(20, self::$volunteerOrg->totalHoursVolunteered($start, $end));
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteeredForVolunteer()
    {
        $volunteer = new Volunteer(TestBootstrap::app('user')->id());

        $this->assertEquals(26, self::$volunteerOrg->totalHoursVolunteered(false, false, $volunteer));
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteerByTag()
    {
        $start = mktime(0, 0, 0, 5, 11, 2013);
        $end = mktime(0, 0, 0, 5, 15, 2013);

        $expected = [
            'test' => 15,
            'test2' => 10,
       ];
        $this->assertEquals($expected, self::$volunteerOrg->totalHoursVolunteeredByTag($start, $end));
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteerByTagForVolunteer()
    {
        $volunteer = new Volunteer(TestBootstrap::app('user')->id());

        $expected = [
            'test' => 15,
            'test2' => 10,
       ];
        $this->assertEquals($expected, self::$volunteerOrg->totalHoursVolunteeredByTag(false, false, $volunteer));
    }

    /**
     * @depends testCreate
     */
    public function testHours()
    {
        $hours = self::$volunteerOrg->hours();

        $this->assertInstanceOf('\infuse\Model\Iterator', $hours);
        $this->assertEquals(9, iterator_count($hours));
    }

    /**
     * @depends testCreate
     */
    public function testHourTags()
    {
        $tags = self::$volunteerOrg->hourTags();

        $expected = ['test', 'test2'];
        $this->assertEquals($expected, $tags);
    }

    /**
     * @depends testCreate
     */
    public function testTopVolunteers()
    {
        $start = mktime(0, 0, 0, 5, 11, 2013);
        $end = mktime(0, 0, 0, 5, 15, 2013);

        $topVolunteers = self::$volunteerOrg->topVolunteers(5, $start, $end);

        $this->assertCount(2, $topVolunteers);
        $this->assertEquals(TestBootstrap::app('user')->id(), $topVolunteers[0]['user']->id());
        $this->assertEquals(15, $topVolunteers[0]['hours']);
        $this->assertEquals(-3, $topVolunteers[1]['user']->id());
        $this->assertEquals(5, $topVolunteers[1]['hours']);
    }

    public function testFirstHourTimestamp()
    {
        $vOrg = new VolunteerOrganization();
        $this->assertEquals(mktime(0, 0, 0, 4, 1, 2013), $vOrg->firstHourTimestamp());

        $vOrg = new VolunteerOrganization();
        $vOrg->created_at = mktime(0, 0, 0, 1, 1, 2014);
        $this->assertEquals(mktime(0, 0, 0, 1, 1, 2014), $vOrg->firstHourTimestamp());

        $vOrg = new VolunteerOrganization();
        $vOrg->organization = self::$org->id();
        $this->assertEquals(mktime(0, 0, 0, 5, 10, 2013), $vOrg->firstHourTimestamp());
    }

    /**
     * @depends testCreate
     */
    public function testInviteVolunteer()
    {
        // invite an existing user by an email address
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$volunteerOrg->inviteVolunteer('test+volunteer@example.com'));
        $this->assertEquals(ORGANIZATION_ROLE_VOLUNTEER, self::$volunteerOrg->relation('organization')->getRoleOfUser(self::$user));

        // invite an existing user by an email address again
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$volunteerOrg->inviteVolunteer('test+volunteer@example.com'));
        $this->assertEquals(ORGANIZATION_ROLE_VOLUNTEER, self::$volunteerOrg->relation('organization')->getRoleOfUser(self::$user));

        // invite an existing user by username
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$volunteerOrg->inviteVolunteer('testvolunteer2'));
        $this->assertEquals(ORGANIZATION_ROLE_VOLUNTEER, self::$volunteerOrg->relation('organization')->getRoleOfUser(self::$user2));

        // invite a non-existent user by email address
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$volunteerOrg->inviteVolunteer('temporary+volunteer@example.com'));

        // check that user exists and is temporary
        self::$tempUser = User::findOne(['where' => [
            'user_email' => 'temporary+volunteer@example.com', ]]);
        $this->assertTrue(self::$tempUser->exists());
        $this->assertTrue(self::$tempUser->isTemporary());
        $this->assertEquals(self::$org->id(), self::$tempUser->invited_by);
        $this->assertEquals(ORGANIZATION_ROLE_VOLUNTEER, self::$volunteerOrg->relation('organization')->getRoleOfUser(self::$tempUser));

        // invite a non-existent user by username
        $this->assertFalse(self::$volunteerOrg->inviteVolunteer('badusername'));
    }

    /**
     * @depends testCreate
     */
    public function testOrgsWithUnapprovedHourNotifications()
    {
        $hour = new VolunteerHour();
        $hour->create([
            'organization' => self::$org->id(),
            'uid' => -2,
            'hours' => 5,
            'timestamp' => time(),
            'approved' => false,
       ]);

        self::$volunteerOrg->load();
        $this->assertEquals(1, self::$volunteerOrg->unapproved_hours_notify_count);

        $volunteerOrgs = VolunteerOrganization::orgsWithUnapprovedHourNotifications();

        $this->assertInstanceOf('\infuse\Model\Iterator', $volunteerOrgs);

        $orgs = [];
        foreach ($volunteerOrgs as $org) {
            $orgs[] = $org;
        }

        $this->assertCount(1, $orgs);
        $this->assertEquals(self::$volunteerOrg->id(), $orgs[0]->id());
    }

    /**
     * @depends testCreate
     */
    public function testProcessUnapprovedNotifications()
    {
        $this->assertTrue(VolunteerOrganization::processUnapprovedNotifications(false));

        self::$volunteerOrg->load();
        $this->assertEquals(0, self::$volunteerOrg->unapproved_hours_notify_count);
    }

    /**
     * @depends testCreate
     */
    public function testSendEmail()
    {
        $options = [
            'subject' => 'Test',
            'to' => [
                [
                    'name' => 'test',
                    'email' => 'test@example.com',],],
            'username' => 'test',
            'volunteer_email' => 'test@exmple.com',];

        $this->assertTrue(self::$volunteerOrg->sendEmail('volunteer-application-approved', $options));
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        self::$volunteerOrg->grantAllPermissions();
        $this->assertTrue(self::$volunteerOrg->delete());
    }
}
