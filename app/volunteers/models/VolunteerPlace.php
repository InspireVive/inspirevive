<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\volunteers\models;

use infuse\Model;
use infuse\Utility as U;
use infuse\Validate;
use app\organizations\models\Organization;

class VolunteerPlace extends Model
{
    public static $scaffoldApi;
    public static $autoTimestamps;

    public static $properties = [
        'organization' => [
            'type' => 'number',
            'relation' => 'app\organizations\models\Organization',
            'required' => true,
       ],
        'name' => [
            'type' => 'string',
            'required' => true,
            'validate' => 'string:2',
            'searchable' => true,
       ],
        'place_type' => [
            'type' => 'number',
            'default' => VOLUNTEER_PLACE_INTERNAL,
            'required' => true,
            'admin_type' => 'enum',
            'admin_enum' => [
                VOLUNTEER_PLACE_INTERNAL => 'internal',
                VOLUNTEER_PLACE_EXTERNAL => 'external',],
       ],
        'address' => [
            'type' => 'string',
            'admin_truncate' => false,
            'admin_hidden_property' => true,
       ],
        'coordinates' => [
            'type' => 'string',
            'admin_hidden_property' => true,
       ],

        /* Verification Information */

        'verify_name' => [
            'type' => 'string',
            'null' => true,
            'admin_hidden_property' => true,
            'searchable' => true,
       ],
        'verify_email' => [
            'type' => 'string',
            'null' => true,
            'admin_hidden_property' => true,
            'searchable' => true,
       ],
        'verify_approved' => [
            'type' => 'boolean',
            'default' => false,
            'admin_hidden_property' => true,
       ],
   ];

    private $justApproved = false;

    protected function hasPermission($permission, Model $requester)
    {
        // find permission verified in find()
        if ($permission == 'find' && $requester->isLoggedIn()) {
            return true;
        }

        // create permission verified in preCreateHook()
        if ($permission == 'create' && $requester->isLoggedIn()) {
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

    public static function find(array $params = [])
    {
        $params['where'] = (array) U::array_value($params, 'where');

        $user = self::$injectedApp['user'];
        if (!$user->isAdmin()) {
            if (isset($params['where']['organization'])) {
                $org = new Organization($params['where']['organization']);

                if ($org->getRoleOfUser($user) < Volunteer::ROLE_VOLUNTEER) {
                    return ['models' => [], 'count' => 0];
                }
            } else {
                return ['models' => [], 'count' => 0];
            }
        }

        return parent::find($params);
    }

    ////////////////////////
    // HOOKS
    ////////////////////////

    protected function preCreateHook(&$data)
    {
        $org = new Organization(U::array_value($data, 'organization'));

        // check creator permission
        $requester = $this->app['user'];
        $role = $org->getRoleOfUser($requester);
        if ($role < Volunteer::ROLE_VOLUNTEER && !$requester->isAdmin()) {
            $this->app['errors']->push(['error' => ERROR_NO_PERMISSION]);

            return false;
        }

        // make sure the place name is unique
        $name = U::array_value($data, 'name');
        if (!empty($name) &&
            $name != $this->name &&
            self::totalRecords([
                'organization' => $org->id(),
                'name' => $name, ]) > 0) {
            $errorStack = $this->app['errors'];
            $errorStack->push([
                'error' => ERROR_VOLUNTEER_PLACE_NAME_TAKEN,
                'params' => [
                    'place_name' => $name, ], ]);

            return false;
        }

        // volunteers cannot verify places
        if ($role < Volunteer::ROLE_ADMIN && !$requester->isAdmin()) {
            $data['verify_approved'] = false;
        }

        // geocode
        if (isset($data['address'])) {
            $data['coordinates'] = $this->geocode($data['address']);
        }

        return true;
    }

    protected function postCreateHook()
    {
        // if not verify approved, notify org admin
        if (!$this->verify_approved) {
            $p = $this->toArray();

            $org = $this->relation('organization');
            $o = $org->toArray();

            $success = $this->app['mailer']->queueEmail(
                'volunteer-place-approval-request',
                [
                    'from_email' => $this->app['config']->get('site.email'),
                    'from_name' => $this->app['config']->get('site.name'),
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
                    'place_admin_url' => $org->volunteerOrganization()->manageUrl().'/places/'.$this->_id, ]);
        }
    }

    protected function preSetHook(&$data)
    {
        // make sure the place name is unique
        $name = U::array_value($data, 'name');
        if (!empty($name) &&
            $name != $this->name &&
            self::totalRecords([
                'organization' => $this->organization,
                'name' => $name, ]) > 0) {
            $errorStack = $this->app['errors'];
            $errorStack->push([
                'error' => ERROR_VOLUNTEER_PLACE_NAME_TAKEN,
                'params' => [
                    'place_name' => $name, ], ]);

            return false;
        }

        // geocode
        if (isset($data['address'])) {
            $data['coordinates'] = $this->geocode($data['address']);
        }

        $this->justApproved = isset($data['verify_approved']) &&
            $data['verify_approved'] && !$this->verify_approved;

        return true;
    }

    protected function postSetHook()
    {
        if ($this->place_type == VOLUNTEER_PLACE_EXTERNAL && $this->justApproved) {
            // now that the place is approved
            // we can request verification of all
            // unapproved hours reported at this place
            $hours = VolunteerHour::findAll([
                'where' => [
                    'organization' => $this->organization,
                    'place' => $this->id(),
                    'approved' => false, ], ]);

            foreach ($hours as $hour) {
                $hour->requestThirdPartyVerification();
            }

            $this->justApproved = false;
        }
    }

    /**
     * Checks if this place is setup and approved to verify volunteer hours.
     *
     * @return bool
     */
    public function canApproveHours()
    {
        $email = $this->verify_email;

        return $this->verify_approved && Validate::is($email, 'email');
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
