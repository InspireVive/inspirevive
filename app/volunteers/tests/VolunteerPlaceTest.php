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
use app\volunteers\models\VolunteerPlace;

class VolunteerPlaceTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $volunteerOrg;
    public static $place;
    public static $place2;
    public static $ogUserId;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test Org',
            'email' => 'test@example.com', ]);

        $uid = TestBootstrap::app('user')->id();
        TestBootstrap::app('user')->enableSU();
        self::$volunteerOrg = new VolunteerOrganization();
        self::$volunteerOrg->create([
            'organization' => self::$org->id(),
            'volunteer_coordinator_email' => 'test@example.com',
            'city' => 'Tulsa', ]);

        $volunteer = new Volunteer();
        $volunteer->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'role' => ORGANIZATION_ROLE_ADMIN, ]);
        TestBootstrap::app('user')->disableSU();

        self::$ogUserId = $uid;
    }

    protected function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$volunteerOrg->id());
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
        if (self::$org) {
            self::$org->grantAllPermissions();
            self::$org->delete();
        }
    }

    public function testPermissions()
    {
        $place = new VolunteerPlace();
        $user = new User(-3);

        $this->assertFalse($place->can('view', $user));
        $this->assertFalse($place->can('edit', $user));
        $this->assertFalse($place->can('delete', $user));
    }

    public function testCanApproveHours()
    {
        $place = new VolunteerPlace();

        $this->assertFalse($place->canApproveHours());

        $place->verify_approved = true;
        $place->verify_email = 'blah';
        $this->assertFalse($place->canApproveHours());

        $place->verify_email = 'test@example.com';
        $this->assertTrue($place->canApproveHours());
    }

    public function testGeocode()
    {
        // NOTE requires internet access to pass
        $place = new VolunteerPlace();

        $coord1 = $place->geocode('83 West Miller Street, Orlando, FL 32806');
        $coord2 = $place->geocode('330 S Broadway Los Angeles, CA 90013');
        $this->assertNotEquals($coord1, $coord2);

        $this->assertEquals('', $place->geocode('kajs;dlkfjsdf,j sdfjaslkjfs8fs8w85u2lsdj f8sdf'));
    }

    public function testCreateNoPermission()
    {
        // logout
        TestBootstrap::app()['user'] = new User(GUEST, false);

        $errorStack = TestBootstrap::app('errors');
        $errorStack->clear();

        $place = new VolunteerPlace();
        $this->assertFalse($place->create([
            'organization' => self::$org->id(),
            'name' => 'Internal',
            'address' => '83 West Miller Street, Orlando, FL 32806',
            'place_type' => VOLUNTEER_PLACE_INTERNAL, ]));

        $errors = $errorStack->errors('VolunteerPlace.create');
        $expected = [[
            'error' => 'no_permission',
            'message' => 'You do not have permission to do that',
            'context' => 'VolunteerPlace.create',
            'params' => [],]];
        $this->assertEquals($expected, $errors);
    }

    public function testCreate()
    {
        self::$place = new VolunteerPlace();
        $this->assertTrue(self::$place->create([
            'organization' => self::$org->id(),
            'name' => 'Internal',
            'address' => '83 West Miller Street, Orlando, FL 32806',
            'place_type' => VOLUNTEER_PLACE_INTERNAL, ]));

        // NOTE requires internet access to pass
        $this->assertNotEquals('', self::$place->coordinates);

        self::$place2 = new VolunteerPlace();
        $this->assertTrue(self::$place2->create([
            'organization' => self::$org->id(),
            'name' => 'External',
            'place_type' => VOLUNTEER_PLACE_EXTERNAL,
            'verify_name' => 'Blah blah',
            'verify_email' => 'test@example.com', ]));
    }

    /**
     * @depends testCreate
     */
    public function testCreateNonUnique()
    {
        $errorStack = TestBootstrap::app('errors');
        $errorStack->clear();

        $place = new VolunteerPlace();
        $this->assertFalse($place->create([
            'organization' => self::$org->id(),
            'name' => 'Internal',
            'address' => '83 West Miller Street, Orlando, FL 32806',
            'place_type' => VOLUNTEER_PLACE_INTERNAL, ]));

        $errors = $errorStack->errors('VolunteerPlace.create');
        $expected = [[
            'error' => 'place_name_taken',
            'message' => 'A volunteer place named Internal already exists. Please use the existing place or choose a different name.',
            'context' => 'VolunteerPlace.create',
            'params' => [
                'place_name' => 'Internal',],]];
        $this->assertEquals($expected, $errors);
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        $this->assertTrue(self::$place2->set('verify_approved', true));
    }

    /**
     * @depends testCreate
     */
    public function testEditNonUnique()
    {
        $place = new VolunteerPlace(5);
        $place->organization = self::$org->id();
        $place->name = 'test';

        $this->assertFalse($place->set('name', 'Internal'));
    }

    /**
     * @depends testCreate
     */
    public function testFindNothing()
    {
        // should not be able to see anything without supplying organization
        $places = VolunteerPlace::find()['models'];

        $this->assertCount(0, $places);
    }

    /**
     * @depends testCreate
     */
    public function testFind()
    {
        // try with the organization supplied
        $places = VolunteerPlace::find([
            'where' => [
                'organization' => self::$org->id(), ],
            'sort' => 'id ASC', ])['models'];

        $this->assertCount(2, $places);

        // look for our known models
        $find = [self::$place->id(), self::$place2->id()];
        foreach ($places as $m) {
            if (($key = array_search($m->id(), $find)) !== false) {
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
        self::$place->grantAllPermissions();
        $this->assertTrue(self::$place->delete());
    }
}
