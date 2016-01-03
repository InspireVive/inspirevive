<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use infuse\Database;
use app\organizations\models\Organization;
use app\users\models\User;
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerHour;

class OrganizationTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $org2;
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
            'ip' => '10.0.0.1',
            'about' => 'bio',
        ]);

        Database::delete('Users', ['user_email' => 'test+volunteer2@example.com']);
        self::$user2 = new User();
        self::$user2->grantAllPermissions();
        self::$user2->create([
            'user_email' => 'test+volunteer2@example.com',
            'username' => 'testvolunteer2',
            'user_password' => ['testpassword', 'testpassword'],
            'ip' => '10.0.0.1',
            'about' => 'bio',
        ]);

        Database::delete('Users', ['user_email' => 'temporary+volunteer@example.com']);
        Database::delete('Users', ['user_email' => 'temporary+volunteer2@example.com']);
    }

    public static function tearDownAfterClass()
    {
        $delete = [
            self::$org,
            self::$org2,
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
        $org = new Organization();
        $user = TestBootstrap::app('user');
        $this->assertTrue($org->can('create', $user));
        $this->assertFalse($org->can('view', $user));
        $this->assertFalse($org->can('edit', $user));
        $this->assertFalse($org->can('admin', $user));
    }

    public function testCreate()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        $this->assertTrue(self::$org->create([
            'name' => 'Test Organization',
            'email' => 'test@example.com',
            'username' => 'test'.time()
        ]));

        self::$org2 = new Organization();
        self::$org2->grantAllPermissions();
        $this->assertTrue(self::$org2->create([
            'name' => 'Test Org',
            'email' => 'test@example.com',
            'username' => 'test2'.time()
        ]));

        /* Create some volunteers */

        $user = TestBootstrap::app('user');
        $uid = $user->id();
        $user->enableSU();
        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => $uid,
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_ADMIN,
        ]));
        $user->disableSU();

        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => -2,
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_AWAITING_APPROVAL,
        ]));

        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => -3,
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_VOLUNTEER,
        ]));

        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'organization' => self::$org->id(),
            'uid' => -4,
            'timestamp' => time(),
            'approved' => true,
       ]));

        /* Create some hours */

        for ($i = 0; $i < 5; ++$i) {
            $hour = new VolunteerHour();
            $this->assertTrue($hour->create([
                'organization' => self::$org->id(),
                'uid' => -3,
                'hours' => 1,
                'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
                'approved' => true,
           ]));
        }

        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => -4,
            'hours' => 10,
            'timestamp' => mktime(0, 0, 0, 5, 12, 2013),
            'approved' => true,
       ]));

        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 11,
            'approved' => true,
        ]));

        // unapproved hours
        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'hours' => 10,
            'tags' => ['yo', 'hello', 'test'],
            'approved' => false,
        ]));

        // approved hours
        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'hours' => 5,
            'approved' => true,
            'tags' => ['test'],
        ]));

        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'hours' => 10,
            'approved' => true,
            'tags' => ['test', 'test2'],
        ]));

        // official, outside hours
        $user->enableSU();
        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org2->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 15, 2013),
            'hours' => 12,
            'approved' => true,
        ]));
        $user->disableSU();

        self::$ogUserId = $uid;
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        self::$org->grantAllPermissions();
        $this->assertTrue(self::$org->set('username', 'testing-1-2-3'));

        $this->assertEquals('testing-1-2-3', self::$org->username);
    }

    /**
     * @depends testCreate
     */
    public function testGetRoleOfUser()
    {
        $this->assertEquals(Volunteer::ROLE_NONE, self::$org->getRoleOfUser(new User(-1)));

        $this->assertEquals(Volunteer::ROLE_ADMIN, self::$org->getRoleOfUser(TestBootstrap::app('user')));
    }

    public function testHourWhereParams()
    {
        $org = new Organization(100);

        $this->assertEquals([
            'uid IN ( SELECT uid FROM Volunteers WHERE organization = "100" AND role >= 1 )',
            'organization' => 100, ], $org->hourWhereParams());
        $this->assertEquals([
            'h.uid IN ( SELECT uid FROM Volunteers WHERE organization = "100" AND role >= 1 )',
            'h.organization' => 100, ], $org->hourWhereParams('h'));
    }

    /**
     * @depends testCreate
     */
    public function testUrl()
    {
        $url = self::$org->url();

        $this->assertTrue(is_string($url));
        $this->assertGreaterThan(10, strlen($url));
    }

    /**
     * @depends testCreate
     */
    public function testManageUrl()
    {
        $url = self::$org->manageUrl();

        $this->assertTrue(is_string($url));
        $this->assertGreaterThan(10, strlen($url));
    }

    /**
     * @depends testCreate
     */
    public function testNumVolunteers()
    {
        $this->assertEquals(2, self::$org->numVolunteers());
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteered()
    {
        $start = mktime(0, 0, 0, 5, 11, 2013);
        $end = mktime(0, 0, 0, 5, 15, 2013);

        $this->assertEquals(20, self::$org->totalHoursVolunteered($start, $end));
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteeredForVolunteer()
    {
        $volunteer = new Volunteer(TestBootstrap::app('user')->id());

        $this->assertEquals(26, self::$org->totalHoursVolunteered(false, false, $volunteer));
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
        $this->assertEquals($expected, self::$org->totalHoursVolunteeredByTag($start, $end));
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
        $this->assertEquals($expected, self::$org->totalHoursVolunteeredByTag(false, false, $volunteer));
    }

    /**
     * @depends testCreate
     */
    public function testHours()
    {
        $hours = self::$org->hours();

        $this->assertInstanceOf('infuse\Model\Iterator', $hours);
        $this->assertEquals(9, iterator_count($hours));
    }

    /**
     * @depends testCreate
     */
    public function testHourTags()
    {
        $tags = self::$org->hourTags();

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

        $topVolunteers = self::$org->topVolunteers(5, $start, $end);

        $this->assertCount(2, $topVolunteers);
        $this->assertEquals(TestBootstrap::app('user')->id(), $topVolunteers[0]['user']->id());
        $this->assertEquals(15, $topVolunteers[0]['hours']);
        $this->assertEquals(-3, $topVolunteers[1]['user']->id());
        $this->assertEquals(5, $topVolunteers[1]['hours']);
    }

    /**
     * @depends testCreate
     */
    public function testFirstHourTimestamp()
    {
        $org = new Organization();
        $this->assertEquals(mktime(0, 0, 0, 4, 1, 2013), $org->firstHourTimestamp());

        $org = new Organization();
        $org->created_at = mktime(0, 0, 0, 1, 1, 2014);
        $this->assertEquals(mktime(0, 0, 0, 1, 1, 2014), $org->firstHourTimestamp());

        $this->assertEquals(mktime(0, 0, 0, 5, 10, 2013), self::$org->firstHourTimestamp());
    }

    /**
     * @depends testCreate
     */
    public function testInviteVolunteer()
    {
        // invite an existing user by an email address
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$org->inviteVolunteer('test+volunteer@example.com'));
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$user));

        // invite an existing user by an email address again
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$org->inviteVolunteer('test+volunteer@example.com'));
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$user));

        // invite an existing user by username
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$org->inviteVolunteer('testvolunteer2'));
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$user2));

        // invite a non-existent user by email address
        $this->assertInstanceOf('app\volunteers\models\Volunteer', self::$org->inviteVolunteer('temporary+volunteer@example.com'));

        // check that user exists and is temporary
        self::$tempUser = User::findOne(['where' => [
            'user_email' => 'temporary+volunteer@example.com', ]]);
        $this->assertTrue(self::$tempUser->exists());
        $this->assertTrue(self::$tempUser->isTemporary());
        $this->assertEquals(self::$org->id(), self::$tempUser->invited_by);
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$tempUser));

        // invite a non-existent user by username
        $this->assertFalse(self::$org->inviteVolunteer('badusername'));
    }

    /**
     * @depends testCreate
     */
    public function testOrgsWithUnapprovedHourNotifications()
    {
        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => -2,
            'hours' => 5,
            'timestamp' => time(),
            'approved' => false,
       ]));

        self::$org->load();
        $this->assertEquals(2, self::$org->unapproved_hours_notify_count);

        $orgs = Organization::orgsWithUnapprovedHourNotifications();

        $this->assertInstanceOf('infuse\Model\Iterator', $orgs);

        $orgs2 = [];
        foreach ($orgs as $org) {
            $orgs2[] = $org;
        }

        $this->assertCount(1, $orgs2);
        $this->assertEquals(self::$org->id(), $orgs2[0]->id());
    }

    /**
     * @depends testOrgsWithUnapprovedHourNotifications
     */
    public function testProcessUnapprovedNotifications()
    {
        $this->assertEquals(1, Organization::processUnapprovedNotifications());

        self::$org->load();
        $this->assertEquals(0, self::$org->unapproved_hours_notify_count);
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

        $this->assertTrue(self::$org->sendEmail('volunteer-application-approved', $options));
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
