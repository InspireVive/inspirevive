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
use App\Volunteers\Models\VolunteerHourTag;
use App\Volunteers\Models\VolunteerPlace;
use Infuse\Test;

class VolunteerHourTest extends PHPUnit_Framework_TestCase
{
    public static $org;
    public static $org2;
    public static $place;
    public static $hour;
    public static $hour2;
    public static $hour3;
    public static $hour4;
    public static $hour5;
    public static $ogUser;
    static $user;

    public static function setUpBeforeClass()
    {
        // reset counts
        $user = Test::$app['user'];
        $user->volunteer_hours = 0;
        $user->saveOrFail();
        self::$ogUser = Test::$app['user'];

        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->name = 'Test';
        self::$org->email = 'test@example.com';
        self::$org->username = 'test'.uniqid();
        self::$org->saveOrFail();

        self::$org2 = new Organization();
        self::$org2->grantAllPermissions();
        self::$org2->name = 'Test 2';
        self::$org2->email = 'test2@example.com';
        self::$org2->username = 'test2'.uniqid();
        self::$org2->saveOrFail();

        Test::$app['user']->promoteToSuperUser();
        $volunteer = new Volunteer();
        $volunteer->organization = self::$org->id();
        $volunteer->uid = $user->id();
        $volunteer->role = Volunteer::ROLE_ADMIN;
        $volunteer->saveOrFail();

        $volunteer = new Volunteer();
        $volunteer->organization = self::$org2->id();
        $volunteer->uid = $user->id();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $volunteer->saveOrFail();

        self::$user = new User;
        self::$user->username = 'test'.rand();
        self::$user->email = 'test'.rand().'@example.com';
        self::$user->password = ['testpassword', 'testpassword'];
        self::$user->ip = '10.0.0.1';
        self::$user->about = 'bio';
        self::$user->saveOrFail();
        $volunteer = new Volunteer();
        $volunteer->uid = self::$user->id();
        $volunteer->organization = self::$org->id();
        $volunteer->role = Volunteer::ROLE_AWAITING_APPROVAL;
        $volunteer->saveOrFail();

        Test::$app['user']->demoteToNormalUser();

        self::$place = new VolunteerPlace();
        self::$place->name = 'Test';
        self::$place->organization = self::$org->id();
        self::$place->place_type = VolunteerPlace::EXTERNAL;
        self::$place->verify_approved = true;
        self::$place->verify_name = 'Jared';
        self::$place->verify_email = 'test@example.com';
        self::$place->saveOrFail();
    }

    public function assertPostConditions()
    {
        Test::$app['user'] = self::$ogUser;
    }

    public static function tearDownAfterClass()
    {
        self::$org->grantAllPermissions()->delete();
        self::$org2->grantAllPermissions()->delete();
        self::$user->grantAllPermissions()->delete();
    }

    public function testTimestampToStartOfDay()
    {
        $this->assertEquals(mktime(0, 0, 0, 7, 18, 2013), VolunteerHour::timestampToStartOfDay(mktime(10, 10, 10, 7, 18, 2013)));
    }

    public function testTimestampToEndOfDay()
    {
        $this->assertEquals(mktime(23, 59, 59, 7, 18, 2013), VolunteerHour::timestampToEndOfDay(mktime(10, 10, 10, 7, 18, 2013)));
    }

    public function testPlace()
    {
        $hour = new VolunteerHour();
        $hour->place = self::$place->id();
        $this->assertEquals(self::$place->id(), $hour->place()->id());
    }

    public function testAttributedMessage()
    {
        $user = new User(10);
        $user->email = 'test@example.com';

        $hour = new VolunteerHour();
        $hour->setRelation('uid', $user);
        $hour->volunteer_hours = 5;
        $hour->organization = self::$org->id();

        $expected = [
            [
                'type' => 'string',
                'value' => 'test@example.com volunteered 5 hours at Test!',
           ],
       ];

        $this->assertEquals($expected, $hour->attributedMessage());
    }

    public function testCreateNoPermission()
    {
        // logout
        Test::$app['user'] = new User(User::GUEST_USER);

        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => self::$ogUser->id(),
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 10,
        ]));

        $expected = ['You do not have permission to do that'];
        $this->assertEquals($expected, $hour->getErrors()->all());
    }

    public function testCreateInvalidHours()
    {
        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => Test::$app['user']->id(),
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 20, ]));

        $expected = ['Invalid number of hours - must be between 1 and 12. If you are reporting hours volunteered over multiple days then please create a new entry for each day.'];
        $this->assertEquals($expected, $hour->getErrors()->all());
    }

    public function testCreateInvalidTimestamp()
    {
        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => Test::$app['user']->id(),
            'timestamp' => time() + 86400 * 30,
            'hours' => 5,
        ]));

        $expected = ['Invalid date - cannot report hours that happened in the future.'];
        $this->assertEquals($expected, $hour->getErrors()->all());
    }

    function testCreateUnapprovedVolunteer()
    {
        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => self::$user->id(),
            'hours' => 1,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'approved' => true,
        ]));

        $this->assertEquals(['This volunteer must be approved before hours can be recorded.'], $hour->getErrors()->messages());
    }

    function testCreateNoApprovalPermission()
    {
        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org2->id(),
            'uid' => Test::$app['user']->id(),
            'hours' => 1,
            'timestamp' => mktime(0, 0, 0, 5, 11, 2013),
            'approved' => true,
        ]));

        $this->assertEquals(['Must be an administrator to approve hours.'], $hour->getErrors()->messages());
    }

    public function testCreate()
    {
        $user = Test::$app['user'];

        // add approved hours
        self::$hour = new VolunteerHour();
        $this->assertTrue(self::$hour->create([
            'organization' => self::$org->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 10,
            'place' => self::$place->id(),
            'approved' => true,
        ]));

        $this->assertEquals(mktime(0, 0, 0, 5, 10, 2013), self::$hour->timestamp);
        $this->assertEquals(10, $user->volunteer_hours);

        // add unapproved hours
        self::$hour2 = new VolunteerHour();
        $this->assertTrue(self::$hour2->create([
            'organization' => self::$org->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 5, 2013),
            'place' => self::$place->id(),
            'hours' => 5,
        ]));

        $this->assertFalse(self::$hour2->approved);
        $this->assertTrue(strlen(self::$hour2->approval_link) == 32);
        $this->assertTrue(self::$hour2->verification_requested);
        $this->assertGreaterThan(0, self::$hour2->verification_requested_at);
        $this->assertEquals(10, $user->refresh()->volunteer_hours);

        self::$hour3 = new VolunteerHour();
        $this->assertTrue(self::$hour3->create([
            'organization' => self::$org2->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 20, 2013),
            'hours' => 10,
        ]));

        $this->assertEquals(10, $user->refresh()->volunteer_hours);
        $this->assertFalse(self::$hour3->approved);

        self::$hour4 = new VolunteerHour();
        $this->assertTrue(self::$hour4->create([
            'organization' => self::$org2->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 20, 2013),
            'hours' => 5,
        ]));

        $this->assertEquals(10, $user->refresh()->volunteer_hours);
        $this->assertFalse(self::$hour4->approved);
    }

    /**
     * @depends testCreate
     */
    public function testCreateWithTags()
    {
        $user = Test::$app['user'];
        self::$hour5 = new VolunteerHour();
        self::$hour5->grantAllPermissions();
        $this->assertTrue(self::$hour5->create([
            'organization' => self::$org->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 1, 10, 2013),
            'hours' => 1,
            'approved' => true,
            'tags' => 'this is a bunch of tags tags tags', ]));

        $this->assertEquals(6, VolunteerHourTag::where('hour', self::$hour5->id())->count());

        $this->assertEquals(11, $user->refresh()->volunteer_hours);
        $this->assertTrue(self::$hour5->approved);
    }

    /**
     * @depends testCreate
     */
    public function testToArray()
    {
        $expected = [
            'id' => self::$hour->id(),
            'organization' => self::$org->id(),
            'uid' => Test::$app['user']->id(),
            'timestamp' => mktime(0, 0, 0, 5, 10, 2013),
            'hours' => 10,
            'place' => self::$place->id(),
            'approval_link' => null,
            'approved' => true,
            'verification_requested' => false,
            'verification_requested_at' => null,
            'tags' => [],
            'created_at' => self::$hour->created_at,
            'updated_at' => self::$hour->updated_at,
       ];

        $this->assertEquals($expected, self::$hour->toArray());
    }

    /**
     * @depends testCreate
     */
    public function testCannotEditHours()
    {
        self::$hour->hours = 5000;
        self::$hour->save();
        $this->assertNotEquals(5000, self::$hour->hours);
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        // reset last sent email time for volunteer
        $volunteer = new Volunteer([self::$hour2->uid, self::$hour2->organization]);
        $volunteer->grantAllPermissions();
        $volunteer->last_email_sent_about_hours = 0;
        $this->assertTrue($volunteer->save());
    }

    /**
     * @depends testCreate
     */
    public function testApprove()
    {
        $user = Test::$app['user'];

        self::$hour2->approved = true;
        $this->assertTrue(self::$hour2->save());
        $this->assertEquals(16, $user->refresh()->volunteer_hours);

        self::$hour2->approved = false;
        $this->assertFalse(self::$hour2->save());
    }

    /**
     * @depends testEdit
     * @depends testCreateWithTags
     */
    public function testEditWithTags()
    {
        self::$hour5->tags = 'other tags tags';
        $this->assertTrue(self::$hour5->save());
        $this->assertEquals(2, VolunteerHourTag::where('hour', self::$hour5->id())->count());
    }

    /**
     * @depends testEditWithTags
     */
    public function testTags()
    {
        $tags = self::$hour5->tags();
        $this->assertEquals(['other', 'tags'], $tags);
    }

    /**
     * @depends testCreateWithTags
     */
    public function testQuery()
    {
        // try with the organization supplied
        $hours = VolunteerHour::where('organization', self::$org->id())
            ->sort('id ASC')
            ->all();

        $this->assertCount(3, $hours);

        // look for our known models
        $find = [self::$hour->id(), self::$hour2->id(), self::$hour5->id()];
        foreach ($hours as $m) {
            if (($key = array_search($m->id(), $find)) !== false) {
                unset($find[$key]);
            }
        }
        $this->assertCount(0, $find);
    }

    /**
     * @depends testCreateWithTags
     */
    public function testQueryApprovalLink()
    {
        // should be able to look up hours with an approval link
        $hours = VolunteerHour::where('organization', self::$org2->id())
            ->where('approval_link', self::$hour4->approval_link)
            ->all();

        $this->assertCount(1, $hours);
        $this->assertEquals(self::$hour4->id(), $hours[0]->id());
    }

    /**
     * @depends testCreate
     */
    public function testVolunteerUser()
    {
        $volunteer = self::$hour->volunteer();
        $this->assertInstanceOf('App\Volunteers\Models\Volunteer', $volunteer);
        $this->assertEquals(Test::$app['user']->id(), $volunteer->uid);
    }

    /**
     * @depends testCreate
     */
    public function testApprovalLink()
    {
        $hour = new VolunteerHour();
        $hour->organization = self::$org->id();
        $hour->approval_link = 'test';

        $this->assertEquals(self::$org->url().'/hours/approve/test', $hour->approvalLink());
    }

    /**
     * @depends testCreate
     */
    public function testRejectLink()
    {
        $hour = new VolunteerHour();
        $hour->organization = self::$org->id();
        $hour->approval_link = 'test';

        $this->assertEquals(self::$org->url().'/hours/reject/test', $hour->rejectLink());
    }

    /**
     * @depends testEdit
     */
    public function testRequestThirdPartyVerification()
    {
        self::$hour2->clearCache();
        self::$hour2->verification_requested = false;
        self::$hour2->verification_requested_at = null;

        $this->assertTrue(self::$hour2->requestThirdPartyVerification());

        $this->assertTrue(self::$hour2->verification_requested);
        $this->assertGreaterThan(0, self::$hour2->verification_requested_at);
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        self::$hour->grantAllPermissions();
        $this->assertTrue(self::$hour->delete());

        $user = Test::$app['user'];
        $this->assertEquals(6, $user->refresh()->volunteer_hours);
    }
}
