<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\users\models;

use infuse\Utility as U;
use app\auth\models\AbstractUser;
use app\volunteers\models\VolunteerApplication;
use app\facebook\models\FacebookProfile;
use app\twitter\models\TwitterProfile;
use app\instagram\models\InstagramProfile;

class User extends AbstractUser
{
    /////////////////////////////////////
    // Model Properties
    /////////////////////////////////////

    protected static $escapedFields = ['about'];

    public static $properties = [
        'uid' => [
            'type' => 'number',
            'mutable' => false,
            'admin_hidden_property' => true,
        ],
        'username' => [
            'type' => 'text',
            'validate' => 'alpha_numeric:2',
            'required' => true,
            'unique' => true,
            'searchable' => true,
            'admin_html' => '<a href="/users/{username}" target="_blank">{username}</a>',
        ],
        'user_email' => [
            'type' => 'string',
            'validate' => 'email',
            'required' => true,
            'unique' => true,
            'title' => 'E-mail',
            'searchable' => true,
            'admin_html' => '<a href="mailto:{user_email}">{user_email}</a>',
        ],
        'user_password' => [
            'type' => 'string',
            'validate' => 'matching|password:8',
            'required' => true,
            'title' => 'Password',
            'hidden' => true,
            'admin_type' => 'password',
            'admin_hidden_property' => true,
        ],
        'ip' => [
            'type' => 'string',
            'required' => true,
            'admin_html' => '<a href="http://www.infobyip.com/ip-{ip}.html" target="_blank">{ip}</a>',
            'admin_hidden_property' => true,
        ],
        'enabled' => [
            'type' => 'boolean',
            'validate' => 'boolean',
            'required' => true,
            'default' => true,
            'admin_hidden_property' => true,
        ],
        'about' => [
            'type' => 'string',
            'admin_hidden_property' => true,
        ],
        'invited_by' => [
            'type' => 'number',
            'relation' => 'app\organizations\models\Organization',
            'null' => true,
            'admin_hidden_property' => true,
        ],
        'profile_picture_preference' => [
            'type' => 'string',
            'default' => 'gravatar',
            'admin_hidden_property' => true,
        ],

        /* Social Media Profiles */

        'facebook_id' => [
            'type' => 'number',
            'null' => true,
            'default' => null,
            'relation' => 'app\facebook\models\FacebookProfile',
        ],
        'twitter_id' => [
            'type' => 'number',
            'null' => true,
            'default' => null,
            'relation' => 'app\twitter\models\TwitterProfile',
        ],
        'instagram_id' => [
            'type' => 'number',
            'null' => true,
            'default' => null,
            'relation' => 'app\instagram\models\InstagramProfile',
        ],

        /* Stats */

        'volunteer_hours' => [
            'type' => 'number',
            'default' => 0,
            'admin_hidden_property' => true,
        ],
    ];

    public static $testUser = [
        'username' => 'testuser',
        'ip' => '127.0.0.1',
        'about' => 'bio'
    ];

    public static $usernameProperties = ['username', 'user_email'];

    protected static $verifyTimeWindow = 86400; // one day

    private $application;

    protected function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!isset($exclude['profile_picture'])) {
            $result['profile_picture'] = $this->profilePicture();
        }

        if (!isset($exclude['name'])) {
            $result['name'] = $this->name(true);
        }
    }

    /**
     * Get's the user's name.
     *
     * @param bool $full get full name if true
     *
     * @return string name
     */
    public function name($full = false)
    {
        if ($this->id() == GUEST) {
            return 'Guest';
        } else {
            if ($full && $this->hasCompletedVolunteerApplication()) {
                return $this->volunteerApplication()->fullName();
            }

            if (!empty($this->username)) {
                return $this->username;
            } elseif (!empty($this->user_email)) {
                return $this->user_email;
            }

            return '(not registered)';
        }
    }

    /**
     * Generates the URL for the user's profile page.
     *
     * @return string
     */
    public function url()
    {
        return $this->app['base_url'].'users/'.$this->username;
    }

    /**
     * Generates the URL for the user's profile picture.
     * Could use Facebook, Twitter, Instagram, or Gravatar photo.
     *
     * @param int $size size of the picture (it is square, usually)
     *
     * @return string url
     */
    public function profilePicture($size = 200, $preference = false)
    {
        $order = [$preference];

        if (!$preference) {
            $order = [
                'facebook',
                'twitter',
                'instagram',
                'gravatar', ];

            if ($this->profile_picture_preference) {
                array_unshift($order, $this->profile_picture_preference);
            }
        }

        $url = false;
        foreach ($order as $source) {
            $method = "profilePicture_$source";
            if ($url = $this->$method($size)) {
                break;
            }
        }

        return $url;
    }

    private function profilePicture_facebook($size)
    {
        return ($this->facebookConnected()) ?
            $this->facebookProfile()->profilePicture($size) : false;
    }

    private function profilePicture_twitter($size)
    {
        return ($this->twitterConnected()) ?
            $this->twitterProfile()->profilePicture($size) : false;
    }

    private function profilePicture_instagram($size)
    {
        return ($this->instagramConnected()) ?
            $this->instagramProfile()->profilePicture($size) : false;
    }

    private function profilePicture_gravatar($size)
    {
        $hash = md5(strtolower(trim($this->user_email)));

        return "https://secure.gravatar.com/avatar/$hash?s=$size&d=mm";
    }

    /**
     * Gets the user's volunteer application model.
     * NOTE need to check if it actually exists.
     *
     * @return VolunteerApplication
     */
    public function volunteerApplication()
    {
        if (!$this->application) {
            $this->application = new VolunteerApplication($this->id());
        }

        return $this->application;
    }

    /**
     * Checks if the user has completed the volunteer application.
     *
     * @return bool
     */
    public function hasCompletedVolunteerApplication()
    {
        return $this->volunteerApplication()->exists();
    }

    /////////////////////////
    // SOCIAL MEDIA PROFILES
    /////////////////////////

    /**
     * Checks if the user has a connected Facbeook account
     * WARNING if you want to check if the user is logged in to facebook,
     * use facebookProfile()->isLoggedIn().
     *
     * @return bool
     */
    public function facebookConnected()
    {
        return $this->facebook_id > 0;
    }

    /**
     * Gets the Facebook profile associated with this user.
     *
     * @return InstagramProfile
     */
    public function facebookProfile()
    {
        return $this->relation('facebook_id');
    }

    /**
     * Checks if the user has a connected Twitter account
     * WARNING if you want to check if the user is logged in to twitter,
     * use twitterProfile()->isLoggedIn().
     *
     * @return bool
     */
    public function twitterConnected()
    {
        return $this->twitter_id > 0;
    }

    /**
     * Gets the Twitter profile associated with this user.
     *
     * @return TwitterProfile
     */
    public function twitterProfile()
    {
        return $this->relation('twitter_id');
    }

    /**
     * Checks if the user has a connected Instagram account
     * WARNING if you want to check if the user is logged in to instagram,
     * use instagramProfile()->isLoggedIn().
     *
     * @return bool
     */
    public function instagramConnected()
    {
        return $this->instagram_id > 0;
    }

    /**
     * Gets the Instagram profile associated with this user.
     *
     * @return InstagramProfile
     */
    public function instagramProfile()
    {
        return $this->relation('instagram_id');
    }

    /////////////////////////
    // STATS
    /////////////////////////

    /**
     * Increments stats for this model.
     *
     * @param array $delta values to increment stats by
     *
     * @return bool
     */
    public function incrementStats(array $delta)
    {
        // do not run this if nothing is being incremented
        $empty = true;
        foreach ($delta as $stat) {
            if ($stat != 0) {
                $empty = false;
                break;
            }
        }

        if ($empty) {
            return true;
        }

        $this->load();

        $keys = array_keys($delta);

        // start with all values at 0
        $stats = array_fill_keys($keys, 0);

        // fetch the current values for all properties in delta
        $actual = $this->get($keys);
        if (count($actual) == 1) {
            $actual = [$keys[0] => $actual];
        }

        // overwrite with the actual values
        $stats = array_replace($stats, $actual);

        // calculate new stat values from incrementing by delta
        $newStats = self::increment($stats, $delta);

        // perform the update
        $this->grantAllPermissions();

        return $this->set($newStats);
    }

    /**
     * Increments the keys in an input array by some delta.
     * NOTE stats cannot be less than 0.
     *
     * @param array $source values to be incremented
     * @param array $delta  values to be added
     *
     * @return array incremented source
     */
    public static function increment(array $source, array $delta)
    {
        $return = [];

        foreach ($source as $k => $v) {
            $return[$k] = max(0, (int) $v + (int) U::array_value($delta, $k));
        }

        return $return;
    }
}
