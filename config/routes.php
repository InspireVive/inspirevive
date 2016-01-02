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
  'get /signup/finish' => [
    'users\Controller',
    'finishSignup',
  ],
  'post /signup/finish' => [
    'users\Controller',
    'finishSignupPost',
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
    'volunteers\Administration',
    'adminIndex',
  ],

  // volunteers
  'get /organizations/:username/admin/volunteers' => [
    'volunteers\Administration',
    'adminVolunteersBrowse',
  ],
  'get /organizations/:username/admin/groups' => [
    'volunteers\Administration',
    'adminVolunteersBrowse',
  ],
  'get /organizations/:username/admin/volunteers/add' => [
    'volunteers\Administration',
    'adminAddVolunteerForm',
  ],
  'post /organizations/:username/admin/volunteers' => [
    'volunteers\Administration',
    'adminAddVolunteer',
  ],
  'get /organizations/:username/admin/volunteers/add/import' => [
    'volunteers\Administration',
    'adminAddVolunteerImportForm',
  ],
  'get /organizations/:username/admin/volunteers/add/group' => [
    'volunteers\Administration',
    'adminAddVolunteerGroupForm',
  ],
  'get /organizations/:username/admin/volunteers/:id' => [
    'volunteers\Administration',
    'adminVolunteersView',
  ],
  'get /organizations/:username/admin/groups/:id' => [
    'volunteers\Administration',
    'adminVolunteersViewGroup',
  ],
  'post /organizations/:username/admin/volunteers/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'post /organizations/:username/admin/groups/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'delete /organizations/:username/admin/volunteers/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],
  'delete /organizations/:username/admin/groups/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],

  // hours
  'get /organizations/:username/admin/hours/add' => [
    'volunteers\Administration',
    'adminRecordHoursStep1',
  ],
  'get /organizations/:username/admin/hours/add/2' => [
    'volunteers\Administration',
    'adminRecordHoursStep2',
  ],
  'post /organizations/:username/admin/hours/add' => [
    'volunteers\Administration',
    'adminRecordHoursStep3',
  ],
  'post /organizations/:username/admin/hours/add/confirm' => [
    'volunteers\Administration',
    'adminRecordHoursStep4',
  ],
  'get /organizations/:username/admin/hours' => [
    'volunteers\Administration',
    'adminHoursBrowse',
  ],
  'get /organizations/:username/admin/hours/:id' => [
    'volunteers\Administration',
    'adminHoursView',
  ],
  'post /organizations/:username/admin/hours/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'delete /organizations/:username/admin/hours/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],

  // places
  'get /organizations/:username/admin/places' => [
    'volunteers\Administration',
    'adminPlacesBrowse',
  ],
  'get /organizations/:username/admin/places/add' => [
    'volunteers\Administration',
    'adminPlacesAddForm',
  ],
  'post /organizations/:username/admin/places' => [
    'volunteers\Administration',
    'adminPlacesAdd',
  ],
  'get /organizations/:username/admin/places/:id' => [
    'volunteers\Administration',
    'adminPlacesView',
  ],
  'get /organizations/:username/admin/places/:id/edit' => [
    'volunteers\Administration',
    'adminPlacesEditForm',
  ],
  'post /organizations/:username/admin/places/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'delete /organizations/:username/admin/places/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],

  // reports
  'get /organizations/:username/admin/reports' => [
    'volunteers\Administration',
    'adminReports',
  ],
]);
