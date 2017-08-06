<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Volunteers\Models;

use App\Organizations\Models\Organization;
use Pulsar\ACLModel;
use Pulsar\Model;
use Infuse\Application;
use Infuse\Utility as U;
use Pulsar\ModelEvent;

class VolunteerPlace extends ACLModel
{
    const INTERNAL = 0;
    const EXTERNAL = 1;

    public static $autoTimestamps;

    public static $properties = [
        'organization' => [
            'type' => Model::TYPE_INTEGER,
            'relation' => Organization::class,
            'required' => true,
       ],
        'name' => [
            'type' => Model::TYPE_STRING,
            'required' => true,
            'validate' => 'string:2',
            'searchable' => true,
       ],
        'place_type' => [
            'type' => Model::TYPE_INTEGER,
            'default' => self::INTERNAL,
            'required' => true,
            'admin_type' => 'enum',
            'admin_enum' => [
                self::INTERNAL => 'internal',
                self::EXTERNAL => 'external',],
       ],
        'address' => [
            'type' => Model::TYPE_STRING,
            'admin_truncate' => false,
            'admin_hidden_property' => true,
       ],
        'coordinates' => [
            'type' => Model::TYPE_STRING,
            'admin_hidden_property' => true,
       ],

        /* Verification Information */

        'verify_name' => [
            'type' => Model::TYPE_STRING,
            'null' => true,
            'admin_hidden_property' => true,
            'searchable' => true,
       ],
        'verify_email' => [
            'type' => Model::TYPE_STRING,
            'validate' => 'email',
            'null' => true,
            'admin_hidden_property' => true,
            'searchable' => true,
       ],
        'verify_approved' => [
            'type' => Model::TYPE_BOOLEAN,
            'default' => false,
            'admin_hidden_property' => true,
       ],
   ];

    private $_wasApproved = false;

    protected function hasPermission($permission, Model $requester)
    {
        // find permission verified in find()
        if ($permission == 'find' && $requester->isSignedIn()) {
            return true;
        }

        // create permission verified in preCreateHook()
        if ($permission == 'create' && $requester->isSignedIn()) {
            return true;
        }

        $orgModel = $this->relation('organization');

        if (in_array($permission, ['view', 'edit', 'delete']) &&
            $orgModel &&
            $orgModel->getRoleOfUser($requester) == Volunteer::ROLE_ADMIN) {
            return true;
        }

        return $requester->isAdmin();
    }

    protected function initialize()
    {
        parent::initialize();

        self::creating([self::class, 'preCreateHook']);
        self::created([self::class, 'postCreateHook']);
        self::updating([self::class, 'preUpdateHook']);
        self::updated([self::class, 'postUpdateHook']);
    }

    ////////////////////////
    // HOOKS
    ////////////////////////

    static function preCreateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        $org = $model->relation('organization');

        // check creator permission
        $requester = $model->getApp()['user'];
        $role = $org->getRoleOfUser($requester);
        if ($role < Volunteer::ROLE_VOLUNTEER && !$requester->isAdmin()) {
            $model->getErrors()->add(ERROR_NO_PERMISSION);
            $event->stopPropagation();
            return;
        }

        // make sure the place name is unique
        $name = $model->name;
        $query = self::where('organization', $org->id())
            ->where('name', $name);
        if (!empty($name) && $query->count() > 0) {
            $model->getErrors()->add(ERROR_VOLUNTEER_PLACE_NAME_TAKEN, ['place_name' => $name]);
            $event->stopPropagation();
            return;
        }

        // volunteers cannot verify places
        if ($role < Volunteer::ROLE_ADMIN && !$requester->isAdmin()) {
            $model->verify_approved = false;
        }

        // geocode
        if ($model->address) {
            $model->coordinates = $model->geocode($model->address);
        }
    }

    static function postCreateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        // if not verify approved, notify org admin
        if (!$model->verify_approved) {
            $p = $model->toArray();

            $org = $model->relation('organization');
            $o = $org->toArray();

            $success = $model->getApp()['mailer']->queueEmail(
                'volunteer-place-approval-request',
                [
                    'from_email' => $model->getApp()['config']->get('app.email'),
                    'from_name' => $model->getApp()['config']->get('app.name'),
                    'to' => [
                        [
                            'name' => $o['name'],
                            'email' => $o['email'], ], ],
                    'subject' => 'New volunteer place requiring your approval on InspireVive',
                    'orgname' => $o['name'],
                    'address' => $p['address'],
                    'coordinator_name' => $p['verify_name'],
                    'coordinator_email' => $p['verify_email'],
                    'place_name' => $p['name'],
                    'place_admin_url' => $org->manageUrl().'/places/'.$model->id(), ]);
        }
    }

    static function preUpdateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        // make sure the place name is unique
        $name = $model->name;
        if (!empty($name) &&
            $name != $model->name &&
            self::where('organization', $model->organization)
                ->where('name', $name)
                ->count() > 0) {
            $model->getErrors()->add(ERROR_VOLUNTEER_PLACE_NAME_TAKEN, ['place_name' => $name]);
            $event->stopPropagation();
            return;
        }

        // geocode
        if ($model->address) {
            $model->coordinates = $model->geocode($model->address);
        }

        $model->_wasApproved = $model->verify_approved && !$model->ignoreUnsaved()->verify_approved;
    }

    static function postUpdateHook(ModelEvent $event)
    {
        $model = $event->getModel();

        if ($model->place_type == self::EXTERNAL && $model->_wasApproved) {
            // now that the place is approved
            // we can request verification of all
            // unapproved hours reported at this place
            $hours = VolunteerHour::where('organization', $model->organization)
                ->where('place', $model->id())
                ->where('approved', false)
                ->all();

            foreach ($hours as $hour) {
                $hour->requestThirdPartyVerification();
            }

            $model->_wasApproved = false;
        }
    }

    ///////////////////////
    /// Getters
    ///////////////////////

    /**
     * Checks if this place is setup and approved to verify volunteer hours.
     *
     * @return bool
     */
    public function canApproveHours()
    {
        return $this->verify_approved && $this->verify_email;
    }

    /**
     * Gets the coordinates for an address.
     *
     * @return string
     */
    public function geocode($address)
    {
        // format this string with the appropriate latitude longitude
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($address);
        // make the HTTP request
        $response = @file_get_contents($url);
        if (!$response) {
            return '';
        }

        // parse the json response
        $jsondata = json_decode($response, true);

        $result = '';
        // if we get a placemark array and the status was good, get the address
        if (is_array($jsondata) && $jsondata ['status'] == 'OK') {
            // get first result
            $result = reset($jsondata['results']);
            // get coordinates
            $result = $result['geometry']['location']['lat'].','.$result['geometry']['location']['lng'];
        }

        return $result;
    }
}
