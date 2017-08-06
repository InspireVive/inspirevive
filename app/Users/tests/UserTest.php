<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Users\Models\User;
use Infuse\Test;

class UserTest extends PHPUnit_Framework_TestCase
{
    public static $user;

    public static function setUpBeforeClass()
    {
        Test::$app['database']->getDefault()
            ->delete('Users')
            ->where('email', 'test+user@example.com')
            ->execute();
    }

    public static function tearDownAfterClass()
    {
        self::$user->grantAllPermissions()->delete();
    }

    public function testRegisterUser()
    {
        self::$user = User::registerUser([
            'email' => 'test+user@example.com',
            'username' => 'testexample',
            'password' => ['testpassword', 'testpassword'],
            'ip' => '127.0.0.1',
            'fb_posts' => 10,
        ]);

        $this->assertInstanceOf('App\Users\Models\User', self::$user);
        $this->assertGreaterThan(0, self::$user->id());
    }

    /**
     * @depends testRegisterUser
     */
    public function testEdit()
    {
        self::$user->grantAllPermissions();
        self::$user->bio = 'test';
        $this->assertTrue(self::$user->save());
    }

    public function testName()
    {
        $user = new User();
        $user->username = 'testexample';
        $this->assertEquals('testexample', $user->name());

        $user->username = '';
        $user->email = 'test+user@example.com';
        $this->assertEquals('test+user@example.com', $user->name());

        $user->email = '';
        $this->assertEquals('(not registered)', $user->name());

        $user = new User(User::GUEST_USER);
        $this->assertEquals('Guest', $user->name());
    }

    public function testUrl()
    {
        $user = new User();
        $user->username = 'test';

        $this->assertEquals('http://localhost:1234/users/test', $user->url());
    }

    public function testProfilePicture()
    {
        $user = new User();
        $user->email = 'j@jaredtking.com';

        $this->assertEquals('https://secure.gravatar.com/avatar/ceed11e6dbd893ecbf3868d2018f0062?s=80&d=mm', $user->profilePicture(80));
    }

    /**
     * @depends testRegisterUser
     */
    public function testIncrementStats()
    {
        self::$user->incrementStats([
            'volunteer_hours' => 10,
        ]);

        $this->assertEquals(10, self::$user->volunteer_hours);
    }
}
