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
use App\Volunteers\Models\VolunteerHour;
use Infuse\Queue\Message;
use Infuse\Test;
use Pulsar\Iterator;

class OrganizationTest extends PHPUnit_Framework_TestCase
{
    public static $org;
    public static $org2;
    public static $tempUser;
    public static $user;
    public static $user2;
    public static $user3;
    public static $user4;
    public static $user5;
    public static $ogUserId;
    static $delete;

    public static function setUpBeforeClass()
    {
        $db = Test::$app['database']->getDefault();
        $db->delete('Users')
            ->where('email', 'test+volunteer@example.com')
            ->execute();
        self::$user = new User();
        self::$user->grantAllPermissions();
        self::$user->create([
            'email' => 'test+volunteer@example.com',
            'username' => 'testvolunteer1',
            'password' => ['testpassword', 'testpassword'],
            'ip' => '10.0.0.1',
            'about' => 'bio',
        ]);
        self::$delete[] = self::$user;

        $db->delete('Users')
            ->where('email', 'test+volunteer2@example.com')
            ->execute();
        self::$user2 = new User();
        self::$user2->grantAllPermissions();
        self::$user2->create([
            'email' => 'test+volunteer2@example.com',
            'username' => 'testvolunteer2',
            'password' => ['testpassword', 'testpassword'],
            'ip' => '10.0.0.1',
            'about' => 'bio',
        ]);
        self::$delete[] = self::$user2;

        $db->delete('Users')
            ->where('email', 'temporary+volunteer@example.com')
            ->execute();
        $db->delete('Users')
            ->where('email', 'temporary+volunteer2@example.com')
            ->execute();
    }

    public static function tearDownAfterClass()
    {
        self::$org->grantAllPermissions()->delete();
        self::$org2->grantAllPermissions()->delete();
        foreach (self::$delete as $model) {
            if ($model && $model->persisted()) {
                $model->grantAllPermissions()->delete();
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
        $app = Test::$app;
        if (!$app['user']->isSignedIn()) {
            $app['user'] = new User(self::$ogUserId, true);
        }
    }

    private function createUser()
    {
        $user = new User;
        $user->username = 'test'.rand();
        $user->email = 'test'.rand().'@example.com';
        $user->password = ['testpassword', 'testpassword'];
        $user->ip = '10.0.0.1';
        $user->about = 'bio';
        $user->saveOrFail();
        self::$delete[] = $user;
        return $user;
    }

    public function testPermissions()
    {
        $org = new Organization();
        $user = Test::$app['user'];
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

        $user = Test::$app['user'];
        $uid = $user->id();
        $user->promoteToSuperUser();
        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => $uid,
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_ADMIN,
        ]));
        $volunteer = new Volunteer();
        $this->assertTrue($volunteer->create([
            'uid' => $uid,
            'organization' => self::$org2->id(),
            'role' => Volunteer::ROLE_VOLUNTEER,
        ]));
        $user->demoteToNormalUser();

        $volunteer = new Volunteer();
        self::$user4 = $this->createUser();
        $this->assertTrue($volunteer->create([
            'uid' => self::$user4->id(),
            'organization' => self::$org->id(),
            'role' => Volunteer::ROLE_VOLUNTEER,
        ]));

        $volunteer = new Volunteer();
        self::$user5 = $this->createUser();
        $this->assertTrue($volunteer->create([
            'organization' => self::$org->id(),
            'uid' => self::$user5->id(),
            'timestamp' => time(),
            'approved' => true,
       ]));

        /* Create some hours */

        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org->id(),
            'uid' => self::$user4->id(),
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

        // outside hours
        $user->promoteToSuperUser();
        $hour = new VolunteerHour();
        $this->assertTrue($hour->create([
            'organization' => self::$org2->id(),
            'uid' => $uid,
            'timestamp' => mktime(0, 0, 0, 5, 15, 2013),
            'hours' => 12,
            'approved' => true,
        ]));
        $user->demoteToNormalUser();

        self::$ogUserId = $uid;
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        self::$org->grantAllPermissions();
        self::$org->username = 'testing-1-2-3';
        $this->assertTrue(self::$org->save());

        $this->assertEquals('testing-1-2-3', self::$org->username);
    }

    /**
     * @depends testCreate
     */
    public function testGetRoleOfUser()
    {
        $this->assertEquals(Volunteer::ROLE_NONE, self::$org->getRoleOfUser(new User(-1)));

        $this->assertEquals(Volunteer::ROLE_ADMIN, self::$org->getRoleOfUser(Test::$app['user']));
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

        $this->assertEquals(25, self::$org->totalHoursVolunteered($start, $end));
    }

    /**
     * @depends testCreate
     */
    public function testTotalHoursVolunteeredForVolunteer()
    {
        $volunteer = new Volunteer(Test::$app['user']->id());

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
        $volunteer = new Volunteer(Test::$app['user']->id());

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

        $this->assertInstanceOf(Iterator::class, $hours);
        $this->assertEquals(5, count($hours));
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
        $this->assertEquals(Test::$app['user']->id(), $topVolunteers[0]['user']->id());
        $this->assertEquals(15, $topVolunteers[0]['hours']);
        $this->assertEquals(self::$user4->id(), $topVolunteers[1]['user']->id());
        $this->assertEquals(10, $topVolunteers[1]['hours']);
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
        $this->assertInstanceOf(Volunteer::class, self::$org->inviteVolunteer('test+volunteer@example.com'));
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$user));

        // invite an existing user by an email address again
        $this->assertInstanceOf(Volunteer::class, self::$org->inviteVolunteer('test+volunteer@example.com'));
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$user));

        // invite a non-existent user by email address
        $this->assertInstanceOf(Volunteer::class, self::$org->inviteVolunteer('temporary+volunteer@example.com'));

        // check that user exists and is temporary
        self::$tempUser = User::where('email', 'temporary+volunteer@example.com')->first();
        self::$delete[] = self::$tempUser;
        $this->assertTrue(self::$tempUser->persisted());
        $this->assertTrue(self::$tempUser->isTemporary());
        $this->assertEquals(self::$org->id(), self::$tempUser->invited_by);
        $this->assertEquals(Volunteer::ROLE_VOLUNTEER, self::$org->getRoleOfUser(self::$tempUser));
    }

    function testInviteVolunteerNotEmail()
    {
        $this->expectException(InvalidArgumentException::class);
        $organization = new Organization();
        $organization->inviteVolunteer('bademail');
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

        $email = self::$org->sendEmail('volunteer-application-approved', $options);
        $this->assertInstanceOf(Message::class, $email);
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
