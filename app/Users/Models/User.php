<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Users\Models;

use App\Organizations\Models\Organization;
use App\Volunteers\Models\VolunteerApplication;
use Infuse\Auth\Models\AbstractUser;
use Pulsar\Model;

class User extends AbstractUser
{
    const GUEST_USER = -2;

    /////////////////////////////////////
    // Model Properties
    /////////////////////////////////////

    protected static $escapedFields = ['about'];

    public static $properties = [
        'username' => [
            'type' => Model::TYPE_STRING,
            'validate' => 'alpha_numeric:2',
            'required' => true,
            'unique' => true,
            'searchable' => true,
            'admin_html' => '<a href="/users/{username}" target="_blank">{username}</a>',
        ],
        'full_name' => [
            'type' => Model::TYPE_STRING,
            'null' => true,
            'searchable' => true,
        ],
        'email' => [
            'type' => Model::TYPE_STRING,
            'validate' => 'email',
            'required' => true,
            'unique' => true,
            'title' => 'Email',
            'searchable' => true,
            'admin_html' => '<a href="mailto:{email}">{email}</a>',
        ],
        'password' => [
            'type' => Model::TYPE_STRING,
            'validate' => 'matching|password:8',
            'required' => true,
            'title' => 'Password',
            'hidden' => true,
            'admin_type' => 'password',
            'admin_hidden_property' => true,
        ],
        'password2' => [
            'null' => true,
        ],
        'ip' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'admin_html' => '<a href="http://www.infobyip.com/ip-{ip}.html" target="_blank">{ip}</a>',
            'admin_hidden_property' => true,
        ],
        'enabled' => [
            'type' => Model::TYPE_BOOLEAN,
            'validate' => 'boolean',
            'required' => true,
            'default' => true,
            'admin_hidden_property' => true,
        ],
        'about' => [
            'type' => Model::TYPE_STRING,
            'admin_hidden_property' => true,
        ],
        'invited_by' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => Organization::class,
            'null' => true,
            'admin_hidden_property' => true,
        ],
        'profile_picture_preference' => [
            'type' => Model::TYPE_STRING,
            'default' => 'gravatar',
            'admin_hidden_property' => true,
        ],

        /* Stats */

        'volunteer_hours' => [
            'type' => Model::TYPE_INTEGER,
            'default' => 0,
            'admin_hidden_property' => true,
        ],
    ];

    /**
     * @var array
     */
    public static $testUser = [
        'username' => 'testuser',
        'ip' => '127.0.0.1',
        'about' => 'bio'
    ];

    /**
     * @var array
     */
    public static $usernameProperties = ['email'];

    /**
     * @var int
     */
    protected static $verifyTimeWindow = 86400; // one day

    /**
     * @var VolunteerApplication
     */
    private $_volunteerApplication = false;

    ////////////////////////
    // Hooks
    ////////////////////////

    protected function toArrayHook(array &$result, array $exclude, array $include, array $expand)
    {
        if (!isset($exclude['profile_picture'])) {
            $result['profile_picture'] = $this->profilePicture();
        }

        if (!isset($exclude['name'])) {
            $result['name'] = $this->name(true);
        }
    }

    protected function setPasswordValue($value)
    {
        $password = $value;
        if (is_array($password)) {
            $password = $password[0];
        }

        $this->rehashPassword($password);

        return $value;
    }

    public function rehashPassword($password)
    {
        $this->password2 = password_hash($password, PASSWORD_DEFAULT);

        return $this;
    }

    ////////////////////////
    // Getters
    ////////////////////////

    /**
     * Get's the user's name.
     *
     * @param bool $full get full name if true
     *
     * @return string name
     */
    public function name($full = false)
    {
        if ($this->id() == self::GUEST_USER) {
            return 'Guest';
        } else {
            if ($full && $name = $this->full_name) {
                return $name;
            }

            if (!empty($this->username)) {
                return $this->username;
            }

            if (!empty($this->email)) {
                return $this->email;
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
        return $this->getApp()['base_url'].'users/'.$this->username;
    }

    /**
     * Generates the URL for the user's profile picture.
     *
     * @param int $size size of the picture (it is square, usually)
     *
     * @return string url
     */
    public function profilePicture($size = 200)
    {
        $hash = md5(strtolower(trim($this->email)));

        return "https://secure.gravatar.com/avatar/$hash?s=$size&d=mm";
    }

    /**
     * Gets the user's volunteer application model.
     *
     * @return VolunteerApplication|null
     */
    public function volunteerApplication()
    {
        if ($this->_volunteerApplication === false) {
            $this->_volunteerApplication = VolunteerApplication::find($this->id());
        }

        return $this->_volunteerApplication;
    }

    /**
     * Checks if the user has completed the volunteer application.
     *
     * @return bool
     */
    public function hasCompletedVolunteerApplication()
    {
        return $this->volunteerApplication() !== null;
    }

    /////////////////////////
    // STATS
    /////////////////////////

    /**
     * Increments stats for this model.
     *
     * @param array $delta key-value mapping of stats to increment
     *
     * @throws \Pulsar\Exception\ModelException if the stats cannot be incremented.
     *
     * @return self
     */
    public function incrementStats(array $delta)
    {
        // refresh to ensure we have the latest values
        $this->refresh();

        // increment stat values by delta
        foreach ($delta as $k => $amount) {
            if ($amount != 0) {
                $this->$k += $amount;
            }
        }

        // perform the update
        $this->grantAllPermissions()->saveOrFail();

        return $this;
    }
}
