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
use App\Volunteers\Models\VolunteerPlace;
use Infuse\Test;

class VolunteerPlaceTest extends PHPUnit_Framework_TestCase
{
    public static $org;
    public static $place;
    public static $place2;
    public static $ogUser;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test Org',
            'email' => 'test@example.com',
            'username' => 'test'.time()
        ]);

        $uid = Test::$app['user']->id();
        Test::$app['user']->promoteToSuperUser();

        $volunteer = new Volunteer();
        $volunteer->create([
            'organization' => self::$org->id(),
            'uid' => $uid,
            'role' => Volunteer::ROLE_ADMIN, ]);
        Test::$app['user']->demoteToNormalUser();

        self::$ogUser = Test::$app['user'];
    }

    protected function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$org->id());
    }

    public function assertPostConditions()
    {
        Test::$app['user'] = self::$ogUser;
    }

    public static function tearDownAfterClass()
    {
        self::$org->grantAllPermissions()->delete();
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
        $place->verify_email = null;
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
        Test::$app['user'] = new User(User::GUEST_USER);

        $place = new VolunteerPlace();
        $this->assertFalse($place->create([
            'organization' => self::$org->id(),
            'name' => 'Internal',
            'address' => '83 West Miller Street, Orlando, FL 32806',
            'place_type' => VolunteerPlace::INTERNAL, ]));

        $expected = ['You do not have permission to do that'];
        $this->assertEquals($expected, $place->getErrors()->all());
    }

    public function testCreate()
    {
        self::$place = new VolunteerPlace();
        $this->assertTrue(self::$place->create([
            'organization' => self::$org->id(),
            'name' => 'Internal',
            'address' => '83 West Miller Street, Orlando, FL 32806',
            'place_type' => VolunteerPlace::INTERNAL, ]));

        // NOTE requires internet access to pass
        $this->assertNotEquals('', self::$place->coordinates);

        self::$place2 = new VolunteerPlace();
        $this->assertTrue(self::$place2->create([
            'organization' => self::$org->id(),
            'name' => 'External',
            'place_type' => VolunteerPlace::EXTERNAL,
            'verify_name' => 'Blah blah',
            'verify_email' => 'test@example.com', ]));
    }

    /**
     * @depends testCreate
     */
    public function testCreateNonUnique()
    {
        $place = new VolunteerPlace();
        $this->assertFalse($place->create([
            'organization' => self::$org->id(),
            'name' => 'Internal',
            'address' => '83 West Miller Street, Orlando, FL 32806',
            'place_type' => VolunteerPlace::INTERNAL, ]));

        $this->assertEquals(['A volunteer place named Internal already exists. Please use the existing place or choose a different name.'], $place->getErrors()->all());
    }

    /**
     * @depends testCreate
     */
    public function testEdit()
    {
        self::$place2->verify_approved = true;
        $this->assertTrue(self::$place2->save());
    }

    /**
     * @depends testCreate
     */
    public function testEditNonUnique()
    {
        $place = new VolunteerPlace();
        $place->organization = self::$org->id();
        $place->name = 'Internal';
        $this->assertFalse($place->save());
    }

    /**
     * @depends testCreate
     */
    public function testQuery()
    {
        // try with the organization supplied
        $places = VolunteerPlace::where('organization', self::$org->id())
            ->sort('id ASC')
            ->all();

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
