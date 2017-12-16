<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Organizations\Jobs\UnapprovedHourNotifications;
use App\Organizations\Models\Organization;
use App\Users\Models\User;
use App\Volunteers\Models\Volunteer;
use App\Volunteers\Models\VolunteerHour;
use Infuse\Queue\Message;
use Infuse\Test;
use Pulsar\Iterator;
use PHPUnit\Framework\TestCase;

class UnapprovedHourNotificationsTest extends TestCase
{
    public static $org;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->name = 'Test Org';
        self::$org->email = 'test@example.com';
        self::$org->username = 'test'.time();
        self::$org->saveOrFail();

        Test::$app['user']->promoteToSuperUser();
        $volunteer = new Volunteer;
        $volunteer->uid = Test::$app['user']->id();
        $volunteer->organization = self::$org->id();
        $volunteer->role = Volunteer::ROLE_VOLUNTEER;
        $volunteer->saveOrFail();

        for ($i = 0; $i < 5; $i++) {
            $hour = new VolunteerHour();
            $hour->organization = self::$org->id();
            $hour->uid = Test::$app['user']->id();
            $hour->hours = 5;
            $hour->timestamp = time();
            $hour->approved = false;
            $hour->saveOrFail();
        }
    }

    public static function tearDownAfterClass()
    {
        if (self::$org) {
            self::$org->grantAllPermissions()->delete();
        }
    }

    private function getJob()
    {
        return new UnapprovedHourNotifications;
    }

    public function testGetOrganizations()
    {
        $this->assertEquals(5, self::$org->refresh()->unapproved_hours_notify_count);

        $job = $this->getJob();
        $orgs = $job->getOrganizations();

        $this->assertInstanceOf(Iterator::class, $orgs);
        $this->assertCount(1, $orgs);
        $this->assertEquals(self::$org->id(), $orgs[0]->id());
    }

    public function testRun()
    {
        $this->assertEquals(1, $this->getJob()->run());
        $this->assertEquals(0, self::$org->refresh()->unapproved_hours_notify_count);
    }
}