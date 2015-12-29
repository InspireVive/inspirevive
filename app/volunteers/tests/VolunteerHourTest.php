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
use app\volunteers\models\VolunteerHour;
use app\volunteers\models\VolunteerHourTag;
use app\volunteers\models\VolunteerPlace;
use app\volunteers\models\VolunteerOrganization;

class VolunteerHourTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $org2;
    public static $volunteerOrg;
    public static $volunteerOrg2;
    public static $place;
    public static $hour;
    public static $hour2;
    public static $hour3;
    public static $hour4;
    public static $hour5;
    public static $ogUserId;

    public static function setUpBeforeClass()
    {
        // reset counts
        $user = TestBootstrap::app('user');
        $user->set([
            'volunteer_hours' => 0,
            'twitter_id' => 100, ]);

        $uid = TestBootstrap::app('user')->id();

        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test',
            'email' => 'test@example.com', ]);

        self::$org2 = new Organization();
        self::$org2->grantAllPermissions();
        self::$org2->create([
            'name' => 'Test 2',
            'email' => 'test2@example.com', ]);

        TestBootstrap::app('user')->enableSU();
        $volunteer = new Volunteer();
        $volunteer->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'role' => ORGANIZATION_ROLE_ADMIN, ]);

        $volunteer = new Volunteer();
        $volunteer->create([
            'organization' => self::$org2->id(),
            'uid' => $uid,
            'role' => ORGANIZATION_ROLE_VOLUNTEER, ]);

        self::$volunteerOrg = new VolunteerOrganization();
        self::$volunteerOrg->create([
            'organization' => self::$org->id(),
            'volunteer_coordinator_email' => 'test@example.com',
            'city' => 'Tulsa', ]);

        self::$volunteerOrg2 = new VolunteerOrganization();
        self::$volunteerOrg2->create([
            'organization' => self::$org2->id(),
            'volunteer_coordinator_email' => 'test2@example.com',
            'city' => 'Tulsa', ]);

        TestBootstrap::app('user')->disableSU();

        self::$place = new VolunteerPlace();
        self::$place->create([
            'name' => 'Test',
            'organization' => self::$org->id(),
            'place_type' => VOLUNTEER_PLACE_EXTERNAL,
            'verify_approved' => true,
            'verify_name' => 'Jared',
            'verify_email' => 'test@example.com', ]);

        self::$ogUserId = $uid;
    }

    protected function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$org->id());
        $this->assertGreaterThan(0, self::$org2->id());
        $this->assertGreaterThan(0, self::$place->id());
    }

    public function assertPostConditions()
    {
        $app = TestBootstrap::app();
        if (!$app['user']->isLoggedIn()) {
            $app['user'] = new User(self::$ogUserId, true);
        }
    }

    public static function tearDownAfterClass()
    {
        $delete = [
            self::$org,
            self::$org2,
       ];

        foreach ($delete as $model) {
            if ($model) {
                $model->grantAllPermissions();
                $model->delete();
            }
        }
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
        $hour = new VolunteerHour();
        $hour->uid = TestBootstrap::app('user')->id();
        $hour->volunteer_hours = 5;
        $hour->organization = self::$org->id();

        $expected = [
            [
                'type' => 'string',
                'value' => 'testuser volunteered 5 hours at Test!',
           ],
       ];

        $this->assertEquals($expected, $hour->attributedMessage());
    }

    public function testCreateNoPermission()
    {
        // logout
        TestBootstrap::app()['user'] = new User(GUEST, false);

        $errorStack = TestBootstrap::app('errors');
        $errorStack->clear();

        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => -2,
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 10, ]));

        $errors = $errorStack->errors('VolunteerHour.create');
        $expected = [[
            'error' => 'no_permission',
            'message' => 'You do not have permission to do that',
            'context' => 'VolunteerHour.create',
            'params' => [],]];
        $this->assertEquals($expected, $errors);
    }

    public function testCreateInvalidHours()
    {
        $errorStack = TestBootstrap::app('errors');
        $errorStack->clear();

        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => TestBootstrap::app('user')->id(),
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 20, ]));

        $errors = $errorStack->errors('VolunteerHour.create');
        $expected = [[
            'error' => 'invalid_num_volunteer_hours',
            'message' => 'Invalid number of hours - must be between 1 and 12. If you are reporting hours volunteered over multiple days then please create a new entry for each day.',
            'context' => 'VolunteerHour.create',
            'params' => [],]];
        $this->assertEquals($expected, $errors);
    }

    public function testCreateInvalidTimestamp()
    {
        $errorStack = TestBootstrap::app('errors');
        $errorStack->clear();

        $hour = new VolunteerHour();
        $this->assertFalse($hour->create([
            'organization' => self::$org->id(),
            'uid' => TestBootstrap::app('user')->id(),
            'timestamp' => time() + 86400 * 30,
            'hours' => 5, ]));

        $errors = $errorStack->errors('VolunteerHour.create');
        $expected = [[
            'error' => 'invalid_hours_timestamp',
            'message' => 'Invalid date - cannot report hours that happened in the future.',
            'context' => 'VolunteerHour.create',
            'params' => [],]];
        $this->assertEquals($expected, $errors);
    }

    public function testCreate()
    {
        $user = TestBootstrap::app('user');

        // add unofficial, approved hours
        self::$hour = new VolunteerHour();
        $this->assertTrue(self::$hour->create([
            'organization' => self::$org->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 10, 2013),
            'hours' => 10,
            'place' => self::$place->id(),
            'approved' => true, ]));

        $this->assertEquals(mktime(0, 0, 0, 5, 10, 2013), self::$hour->timestamp);
        $user->load();
        $this->assertEquals(10, $user->volunteer_hours);

        // add unapproved hours
        self::$hour2 = new VolunteerHour();
        $this->assertTrue(self::$hour2->create([
            'organization' => self::$org->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 5, 2013),
            'place' => self::$place->id(),
            'hours' => 5, ]));

        $this->assertFalse(self::$hour2->approved);
        $this->assertTrue(strlen(self::$hour2->approval_link) == 32);
        $this->assertTrue(self::$hour2->verification_requested);
        $this->assertGreaterThan(0, self::$hour2->verification_requested_at);
        $user->load();
        $this->assertEquals(10, $user->volunteer_hours);

        // add official, unapproved hours
        self::$hour3 = new VolunteerHour();
        $this->assertTrue(self::$hour3->create([
            'organization' => self::$org2->id(),
            'uid' => $user->id(),
            'timestamp' => mktime(10, 10, 10, 5, 20, 2013),
            'hours' => 10,
            // this should fall back to unapproved because current user
            // is not an org admin
            'approved' => true, ]));

        $user->load();
        $this->assertEquals(10, $user->volunteer_hours);
        $this->assertFalse(self::$hour3->approved);

        // add official, unapproved hours for another user
        self::$hour4 = new VolunteerHour();
        $this->assertTrue(self::$hour4->create([
            'organization' => self::$org2->id(),
            'uid' => -100,
            'timestamp' => mktime(10, 10, 10, 5, 20, 2013),
            'hours' => 5, ]));
    }

    /**
     * @depends testCreate
     */
    public function testCreateWithTags()
    {
        self::$hour5 = new VolunteerHour();
        self::$hour5->grantAllPermissions();
        $this->assertTrue(self::$hour5->create([
            'organization' => self::$org->id(),
            'uid' => TestBootstrap::app('user')->id(),
            'timestamp' => mktime(10, 10, 10, 1, 10, 2013),
            'hours' => 1,
            'approved' => true,
            'tags' => 'this is a bunch of tags tags tags', ]));

        $this->assertEquals(6, VolunteerHourTag::totalRecords(['hour' => self::$hour5->id()]));
    }

    /**
     * @depends testCreate
     */
    public function testToArray()
    {
        $expected = [
            'id' => self::$hour->id(),
            'organization' => self::$org->id(),
            'uid' => TestBootstrap::app('user')->id(),
            'timestamp' => mktime(0, 0, 0, 5, 10, 2013),
            'hours' => 10,
            'place' => self::$place->id(),
            'approval_link' => null,
            'approved' => true,
            'verification_requested' => false,
            'verification_requested_at' => null,
            'tags' => [],
       ];

        $this->assertEquals($expected, self::$hour->toArray(['created_at', 'updated_at']));
    }

    /**
     * @depends testCreate
     */
    public function testCannotEdit()
    {
        $hours = self::$hour->hours;

        $this->assertFalse(self::$hour->set('hours', 5000));
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        // reset last sent email time for volunteer
        $volunteer = new Volunteer([self::$hour2->uid, self::$hour2->organization]);
        $volunteer->grantAllPermissions();
        $volunteer->set('last_email_sent_about_hours', 0);
    }

    /**
     * @depends testCreate
     */
    public function testApprove()
    {
        $user = TestBootstrap::app('user');

        $this->assertTrue(self::$hour2->set('approved', true));
        $user->load();
        $this->assertEquals(16, $user->volunteer_hours);

        $this->assertFalse(self::$hour2->set('approved', false));
    }

    /**
     * @depends testEdit
     * @depends testCreateWithTags
     */
    public function testEditWithTags()
    {
        $this->assertTrue(self::$hour5->set('tags', 'other tags tags'));
        $this->assertEquals(2, VolunteerHourTag::totalRecords(['hour' => self::$hour5->id()]));
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
    public function testFind()
    {
        // try with the organization supplied
        $hours = VolunteerHour::find([
            'where' => [
                'organization' => self::$org->id(), ],
            'sort' => 'id ASC', ])['models'];

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
    public function testFindApprovalLink()
    {
        // should be able to look up hours with an approval link
        $hours = VolunteerHour::find([
            'where' => [
                'organization' => self::$org2->id(),
                'approval_link' => self::$hour4->approval_link, ], ])['models'];

        $this->assertCount(1, $hours);
        $this->assertEquals(self::$hour4->id(), $hours[0]->id());
    }

    /**
     * @depends testCreate
     */
    public function testVolunteerUser()
    {
        $volunteer = self::$hour->volunteer();
        $this->assertInstanceOf('app\volunteers\models\Volunteer', $volunteer);
        $this->assertEquals(TestBootstrap::app('user')->id(), $volunteer->uid);
    }

    /**
     * @depends testCreate
     */
    public function testApprovalLink()
    {
        $hour = new VolunteerHour();
        $hour->organization = self::$org->id();
        $hour->approval_link = 'test';

        $this->assertEquals(self::$volunteerOrg->url().'/hours/approve/test', $hour->approvalLink());
    }

    /**
     * @depends testCreate
     */
    public function testRejectLink()
    {
        $hour = new VolunteerHour();
        $hour->organization = self::$org->id();
        $hour->approval_link = 'test';

        $this->assertEquals(self::$volunteerOrg->url().'/hours/reject/test', $hour->rejectLink());
    }

    /**
     * @depends testEdit
     */
    public function testRequestThirdPartyVerification()
    {
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
        $user = TestBootstrap::app('user');

        self::$hour->grantAllPermissions();
        $this->assertTrue(self::$hour->delete());

        $user->load();
        $this->assertEquals(6, $user->volunteer_hours);
    }
}
