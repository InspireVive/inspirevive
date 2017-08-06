<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

$routes = [

    /*
     * Landing
     */

    'GET /' => [
        'App\Pages\Controller',
        'index',
    ],

    /*
     * Authentication
     */

    'GET /login' => [
        'App\Users\Controller',
        'loginForm',
    ],
    'POST /login' => [
        'App\Users\Controller',
        'login',
    ],
    'GET /logout' => [
        'App\Users\Controller',
        'logout',
    ],
    'GET /signup' => [
        'App\Users\Controller',
        'signupForm',
    ],
    'POST /signup' => [
        'App\Users\Controller',
        'signup',
    ],
    'GET /forgot' => [
        'App\Users\Controller',
        'forgotForm',
    ],
    'POST /forgot' => [
        'App\Users\Controller',
        'forgotStep1',
    ],
    'GET /forgot/{id}' => [
        'App\Users\Controller',
        'forgotForm',
    ],
    'POST /forgot/{id}' => [
        'App\Users\Controller',
        'forgotStep2',
    ],
    'GET /users/verifyEmail/{id}' => [
        'App\Users\Controller',
        'verifyEmail',
    ],
    'GET /users/resendVerification' => [
        'App\Users\Controller',
        'sendVerifyEmail'
    ],
    'GET /users/forgot/{id}' => [
        'App\Users\Controller',
        'forgotForm'
    ],

    /*
     * My Profile
     */

    'GET /profile' => [
        'App\Users\Controller',
        'myProfile',
    ],
    'GET /account/{section}' => [
        'App\Users\Controller',
        'accountSettings',
    ],
    'GET /account' => [
        'App\Users\Controller',
        'accountSettings',
    ],
    'POST /account' => [
        'App\Users\Controller',
        'editAccountSettings',
    ],

    /*
     * User Profiles
     */

    'GET /users/{username}' => [
        'App\Users\Controller',
        'userProfile'
    ],

    /*
     * Volunteer Applications
     */

    'GET /volunteers/application' => [
        'App\Volunteers\Controller',
        'volunteerApplication',
    ],
    'POST /volunteers/application' => [
        'App\Volunteers\Controller',
        'submitVolunteerApplication',
    ],
    'GET /volunteers/application/thanks' => [
        'App\Volunteers\Controller',
        'volunteerApplicationThanks',
    ],

    /*
     * Volunteer Hour Reporting
     */

    'GET /organizations/{username}/hours/report' => [
        'App\Volunteers\Controller',
        'reportHoursStep1',
    ],
    'GET /organizations/{username}/places/add' => [
        'App\Volunteers\Controller',
        'addVolunteerPlaceForm',
    ],
    'POST /organizations/{username}/places/add' => [
        'App\Volunteers\Controller',
        'addVolunteerPlace',
    ],
    'GET /organizations/{username}/hours/report/2' => [
        'App\Volunteers\Controller',
        'reportHoursStep2',
    ],
    'POST /organizations/{username}/hours/report' => [
        'App\Volunteers\Controller',
        'reportHoursStep3',
    ],
    'GET /organizations/{username}/hours/thanks' => [
        'App\Volunteers\Controller',
        'reportHoursThanks',
    ],
    'GET /organizations/{username}/volunteers/approve/{approval_link}' => [
        'App\Volunteers\Controller',
        'approveVolunteer',
    ],
    'GET /organizations/{username}/volunteers/reject/{approval_link}' => [
        'App\Volunteers\Controller',
        'rejectVolunteer',
    ],
    'GET /organizations/{username}/hours/approve/{approval_link}' => [
        'App\Volunteers\Controller',
        'approveHours',
    ],
    'GET /organizations/{username}/hours/reject/{approval_link}' => [
        'App\Volunteers\Controller',
        'rejectHours',
    ],

    /*
     * Volunteer Hub
     */

    'GET /organizations/{username}' => [
        'App\Volunteers\Controller',
        'volunteerHub',
    ],
    'POST /organizations/{username}/volunteers' => [
        'App\Volunteers\Controller',
        'joinOrganization',
    ],
    'DELETE /organizations/{username}/volunteers' => [
        'App\Volunteers\Controller',
        'unjoinOrganization',
    ],

    /*
     * Volunteer Organization Admin
     */

    // dashboard
    'GET /organizations/{username}/admin' => [
        'App\Admin\Controller',
        'index',
    ],

    // volunteers
    'GET /organizations/{username}/admin/volunteers' => [
        'App\Admin\Controller',
        'volunteersBrowse',
    ],
    'GET /organizations/{username}/admin/volunteers/lookupUsername' => [
        'App\Admin\Controller',
        'volunteersLookupByUsername',
    ],
    'GET /organizations/{username}/admin/groups' => [
        'App\Admin\Controller',
        'volunteersBrowse',
    ],
    'GET /organizations/{username}/admin/volunteers/add' => [
        'App\Admin\Controller',
        'addVolunteerForm',
    ],
    'POST /organizations/{username}/admin/volunteers' => [
        'App\Admin\Controller',
        'addVolunteer',
    ],
    'GET /organizations/{username}/admin/volunteers/add/import' => [
        'App\Admin\Controller',
        'addVolunteerImportForm',
    ],
    'GET /organizations/{username}/admin/volunteers/add/group' => [
        'App\Admin\Controller',
        'addVolunteerGroupForm',
    ],
    'GET /organizations/{username}/admin/volunteers/{id}' => [
        'App\Admin\Controller',
        'volunteersView',
    ],
    'GET /organizations/{username}/admin/groups/{id}' => [
        'App\Admin\Controller',
        'volunteersViewGroup',
    ],
    'POST /organizations/{username}/admin/volunteers/{id}' => [
        'App\Admin\Controller',
        'modelEdit',
    ],
    'POST /organizations/{username}/admin/groups/{id}' => [
        'App\Admin\Controller',
        'modelEdit',
    ],
    'DELETE /organizations/{username}/admin/volunteers/{id}' => [
        'App\Admin\Controller',
        'modelDelete',
    ],
    'DELETE /organizations/{username}/admin/groups/{id}' => [
        'App\Admin\Controller',
        'modelDelete',
    ],

    // hours
    'GET /organizations/{username}/admin/hours/add' => [
        'App\Admin\Controller',
        'recordHoursStep1',
    ],
    'GET /organizations/{username}/admin/hours/add/2' => [
        'App\Admin\Controller',
        'recordHoursStep2',
    ],
    'POST /organizations/{username}/admin/hours/add' => [
        'App\Admin\Controller',
        'recordHoursStep3',
    ],
    'POST /organizations/{username}/admin/hours/add/confirm' => [
        'App\Admin\Controller',
        'recordHoursStep4',
    ],
    'GET /organizations/{username}/admin/hours' => [
        'App\Admin\Controller',
        'hoursBrowse',
    ],
    'GET /organizations/{username}/admin/hours/{id}' => [
        'App\Admin\Controller',
        'hoursView',
    ],
    'POST /organizations/{username}/admin/hours/{id}' => [
        'App\Admin\Controller',
        'modelEdit',
    ],
    'DELETE /organizations/{username}/admin/hours/{id}' => [
        'App\Admin\Controller',
        'modelDelete',
    ],

    // places
    'GET /organizations/{username}/admin/places' => [
        'App\Admin\Controller',
        'placesBrowse',
    ],
    'GET /organizations/{username}/admin/places/add' => [
        'App\Admin\Controller',
        'placesAddForm',
    ],
    'POST /organizations/{username}/admin/places' => [
        'App\Admin\Controller',
        'placesAdd',
    ],
    'GET /organizations/{username}/admin/places/{id}' => [
        'App\Admin\Controller',
        'placesView',
    ],
    'GET /organizations/{username}/admin/places/{id}/edit' => [
        'App\Admin\Controller',
        'placesEditForm',
    ],
    'POST /organizations/{username}/admin/places/{id}' => [
        'App\Admin\Controller',
        'modelEdit',
    ],
    'DELETE /organizations/{username}/admin/places/{id}' => [
        'App\Admin\Controller',
        'modelDelete',
    ],

    // reports
    'GET /organizations/{username}/admin/reports' => [
        'App\Admin\Controller',
        'reports',
    ],
    'POST /reports' => [
        'App\Reports\Controller',
        'makeReport',
        ['no_csrf' => true]
    ],
];

return ['routes' => $routes];