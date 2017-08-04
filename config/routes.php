<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

$apiRoutes = include 'routes_api.php';

return array_merge($apiRoutes, [

  /*
   * Landing
   */

  '/' => [
    'pages\Controller',
  ],

  /*
   * Authentication
   */

  'get /login' => [
    'users\Controller',
    'loginForm',
  ],
  'post /login' => [
    'users\Controller',
    'login',
  ],
  'get /logout' => [
    'users\Controller',
    'logout',
  ],
  'get /signup' => [
    'users\Controller',
    'signupForm',
  ],
  'post /signup' => [
    'users\Controller',
    'signup',
  ],
  'get /forgot' => [
    'users\Controller',
    'forgotForm',
  ],
  'post /forgot' => [
    'users\Controller',
    'forgotStep1',
  ],
  'get /forgot/:id' => [
    'users\Controller',
    'forgotForm',
  ],
  'post /forgot/:id' => [
    'users\Controller',
    'forgotStep2',
  ],

  /*
   * My Profile
   */

  'get /profile' => [
    'users\Controller',
    'myProfile',
  ],
  'get /account/:section' => [
    'users\Controller',
    'accountSettings',
  ],
  'get /account' => [
    'users\Controller',
    'accountSettings',
  ],
  'post /account' => [
    'users\Controller',
    'editAccountSettings',
  ],

  /*
   * Volunteer Applications
   */

  'get /volunteers/application' => [
    'volunteers\Controller',
    'volunteerApplication',
  ],
  'post /volunteers/application' => [
    'volunteers\Controller',
    'submitVolunteerApplication',
  ],
  'get /volunteers/application/thanks' => [
    'volunteers\Controller',
    'volunteerApplicationThanks',
  ],

  /*
   * Volunteer Hour Reporting
   */

  'get /organizations/:username/hours/report' => [
    'volunteers\Controller',
    'reportHoursStep1',
  ],
  'get /organizations/:username/places/add' => [
    'volunteers\Controller',
    'addVolunteerPlaceForm',
  ],
  'post /organizations/:username/places/add' => [
    'volunteers\Controller',
    'addVolunteerPlace',
  ],
  'get /organizations/:username/hours/report/2' => [
    'volunteers\Controller',
    'reportHoursStep2',
  ],
  'post /organizations/:username/hours/report' => [
    'volunteers\Controller',
    'reportHoursStep3',
  ],
  'get /organizations/:username/hours/thanks' => [
    'volunteers\Controller',
    'reportHoursThanks',
  ],
  'get /organizations/:username/volunteers/approve/:approval_link' => [
    'volunteers\Controller',
    'approveVolunteer',
  ],
  'get /organizations/:username/volunteers/reject/:approval_link' => [
    'volunteers\Controller',
    'rejectVolunteer',
  ],
  'get /organizations/:username/hours/approve/:approval_link' => [
    'volunteers\Controller',
    'approveHours',
  ],
  'get /organizations/:username/hours/reject/:approval_link' => [
    'volunteers\Controller',
    'rejectHours',
  ],

  /*
   * Volunteer Hub
   */

  'get /organizations/:username' => [
    'volunteers\Controller',
    'volunteerHub',
  ],
  'post /organizations/:username/volunteers' => [
    'volunteers\Controller',
    'joinOrganization',
  ],
  'delete /organizations/:username/volunteers' => [
    'volunteers\Controller',
    'unjoinOrganization',
  ],

  /*
   * Console Commands
   */

  'get /volunteers/resendVerificationRequests/:org' => [
    'volunteers\CLI',
    'resendVerificationRequests',
  ],
  'get /volunteers/markInactive' => [
    'volunteers\CLI',
    'markInactive',
  ],

  /*
   * Volunteer Organization Admin
   */

  // dashboard
  'get /organizations/:username/admin' => [
    'volunteers_admin\Controller',
    'index',
  ],

  // volunteers
  'get /organizations/:username/admin/volunteers' => [
    'volunteers_admin\Controller',
    'volunteersBrowse',
  ],
  'get /organizations/:username/admin/volunteers/lookupUsername' => [
    'volunteers_admin\Controller',
    'volunteersLookupByUsername',
  ],
  'get /organizations/:username/admin/groups' => [
    'volunteers_admin\Controller',
    'volunteersBrowse',
  ],
  'get /organizations/:username/admin/volunteers/add' => [
    'volunteers_admin\Controller',
    'addVolunteerForm',
  ],
  'post /organizations/:username/admin/volunteers' => [
    'volunteers_admin\Controller',
    'addVolunteer',
  ],
  'get /organizations/:username/admin/volunteers/add/import' => [
    'volunteers_admin\Controller',
    'addVolunteerImportForm',
  ],
  'get /organizations/:username/admin/volunteers/add/group' => [
    'volunteers_admin\Controller',
    'addVolunteerGroupForm',
  ],
  'get /organizations/:username/admin/volunteers/:id' => [
    'volunteers_admin\Controller',
    'volunteersView',
  ],
  'get /organizations/:username/admin/groups/:id' => [
    'volunteers_admin\Controller',
    'volunteersViewGroup',
  ],
  'post /organizations/:username/admin/volunteers/:id' => [
    'volunteers_admin\Controller',
    'modelEdit',
  ],
  'post /organizations/:username/admin/groups/:id' => [
    'volunteers_admin\Controller',
    'modelEdit',
  ],
  'delete /organizations/:username/admin/volunteers/:id' => [
    'volunteers_admin\Controller',
    'modelDelete',
  ],
  'delete /organizations/:username/admin/groups/:id' => [
    'volunteers_admin\Controller',
    'modelDelete',
  ],

  // hours
  'get /organizations/:username/admin/hours/add' => [
    'volunteers_admin\Controller',
    'recordHoursStep1',
  ],
  'get /organizations/:username/admin/hours/add/2' => [
    'volunteers_admin\Controller',
    'recordHoursStep2',
  ],
  'post /organizations/:username/admin/hours/add' => [
    'volunteers_admin\Controller',
    'recordHoursStep3',
  ],
  'post /organizations/:username/admin/hours/add/confirm' => [
    'volunteers_admin\Controller',
    'recordHoursStep4',
  ],
  'get /organizations/:username/admin/hours' => [
    'volunteers_admin\Controller',
    'hoursBrowse',
  ],
  'get /organizations/:username/admin/hours/:id' => [
    'volunteers_admin\Controller',
    'hoursView',
  ],
  'post /organizations/:username/admin/hours/:id' => [
    'volunteers_admin\Controller',
    'modelEdit',
  ],
  'delete /organizations/:username/admin/hours/:id' => [
    'volunteers_admin\Controller',
    'modelDelete',
  ],

  // places
  'get /organizations/:username/admin/places' => [
    'volunteers_admin\Controller',
    'placesBrowse',
  ],
  'get /organizations/:username/admin/places/add' => [
    'volunteers_admin\Controller',
    'placesAddForm',
  ],
  'post /organizations/:username/admin/places' => [
    'volunteers_admin\Controller',
    'placesAdd',
  ],
  'get /organizations/:username/admin/places/:id' => [
    'volunteers_admin\Controller',
    'placesView',
  ],
  'get /organizations/:username/admin/places/:id/edit' => [
    'volunteers_admin\Controller',
    'placesEditForm',
  ],
  'post /organizations/:username/admin/places/:id' => [
    'volunteers_admin\Controller',
    'modelEdit',
  ],
  'delete /organizations/:username/admin/places/:id' => [
    'volunteers_admin\Controller',
    'modelDelete',
  ],

  // reports
  'get /organizations/:username/admin/reports' => [
    'volunteers_admin\Controller',
    'reports',
  ],
]);
