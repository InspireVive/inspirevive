<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use infuse\Database;
use app\users\models\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    public static $user;

    public static function setUpBeforeClass()
    {
        Database::delete('Users', ['user_email' => 'test@example.com']);
    }

    public static function tearDownAfterClass()
    {
        foreach ([self::$user, self::$twitterProfile, self::$instagramProfile] as $m) {
            if ($m) {
                $m->grantAllPermissions();
                $m->delete();
            }
        }
    }

    public function testRegisterUser()
    {
        self::$user = User::registerUser([
            'user_email' => 'test@example.com',
            'username' => 'testexample',
            'user_password' => ['testpassword', 'testpassword'],
            'ip' => '127.0.0.1',
            'fb_posts' => 10,
        ]);

        $this->assertInstanceOf('app\users\models\User', self::$user);
        $this->assertGreaterThan(0, self::$user->id());
    }

    /**
     * @depends testRegisterUser
     */
    public function testEdit()
    {
        self::$user->grantAllPermissions();
        $this->assertTrue(self::$user->set('bio', 'test'));
    }

    public function testName()
    {
        $user = new User();
        $user->username = 'testexample';
        $this->assertEquals('testexample', $user->name());

        $user->username = '';
        $user->user_email = 'test@example.com';
        $this->assertEquals('test@example.com', $user->name());

        $user->user_email = '';
        $this->assertEquals('(not registered)', $user->name());

        $user = new User(GUEST);
        $this->assertEquals('Guest', $user->name());
    }

    public function testUrl()
    {
        $user = new User();
        $user->username = 'test';

        $this->assertEquals(TestBootstrap::app('base_url').'users/test', $user->url());
    }

    public function testProfilePicture()
    {
        $user = new User();
        $user->user_email = 'j@jaredtking.com';

        $this->assertEquals('https://secure.gravatar.com/avatar/ceed11e6dbd893ecbf3868d2018f0062?s=80&d=mm', $user->profilePicture(80));
    }

    public function testProfilePicturePreference()
    {
        $user = new User();
        $user->facebook_id = 100;
        $user->instagram_id = 100;
        $user->profile_picture_preference = 'instagram';

        $this->assertEquals('instagram_profile_picture', $user->profilePicture());
    }

    public function testFacebookConnected()
    {
        $user = new User();
        $this->assertFalse($user->facebookConnected());

        $user->facebook_id = 100;
        $this->assertTrue($user->facebookConnected());
    }

    public function testFacebookProfile()
    {
        $user = new User();
        $user->facebook_id = 100;

        $profile = $user->facebookProfile();
        $this->assertInstanceOf('app\facebook\models\FacebookProfile', $profile);
        $this->assertEquals(100, $profile->id());
    }

    public function testProfilePictureFacebook()
    {
        $user = new User();
        $user->facebook_id = 100;
        $user->profile_picture_preference = 'facebook';

        $this->assertEquals('https://graph.facebook.com/100/picture?width=80&height=80', $user->profilePicture(80));
    }

    public function testTwitterConnected()
    {
        $user = new User();
        $this->assertFalse($user->twitterConnected());

        $user->twitter_id = 100;
        $this->assertTrue($user->twitterConnected());
    }

    public function testTwitterProfile()
    {
        $user = new User();
        $user->twitter_id = 100;

        $profile = $user->twitterProfile();
        $this->assertInstanceOf('app\twitter\models\TwitterProfile', $profile);
        $this->assertEquals(100, $profile->id());
    }

    public function testProfilePictureTwitter()
    {
        $user = new User();
        $user->twitter_id = 100;
        $user->profile_picture_preference = 'twitter';

        $this->assertEquals('twitter_profile_picture', $user->profilePicture(80));
    }

    public function testInstagramConnected()
    {
        $user = new User();
        $this->assertFalse($user->instagramConnected());

        $user->instagram_id = 100;
        $this->assertTrue($user->instagramConnected());
    }

    public function testInstagramProfile()
    {
        $user = new User();
        $user->instagram_id = 100;

        $profile = $user->instagramProfile();
        $this->assertInstanceOf('app\instagram\models\InstagramProfile', $profile);
        $this->assertEquals(100, $profile->id());
    }

    public function testProfilePictureInstagram()
    {
        $user = new User();
        $user->instagram_id = 100;
        $user->profile_picture_preference = 'instagram';

        $this->assertEquals('instagram_profile_picture', $user->profilePicture(80));
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
