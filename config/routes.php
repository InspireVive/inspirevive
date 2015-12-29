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

  'get /organizations/:slug/hours/report' => [
    'volunteers\Controller',
    'reportHoursStep1',
  ],
  'get /organizations/:slug/places/add' => [
    'volunteers\Controller',
    'addVolunteerPlaceForm',
  ],
  'post /organizations/:slug/places/add' => [
    'volunteers\Controller',
    'addVolunteerPlace',
  ],
  'get /organizations/:slug/hours/report/2' => [
    'volunteers\Controller',
    'reportHoursStep2',
  ],
  'post /organizations/:slug/hours/report' => [
    'volunteers\Controller',
    'reportHoursStep3',
  ],
  'get /organizations/:slug/hours/thanks' => [
    'volunteers\Controller',
    'reportHoursThanks',
  ],
  'get /organizations/:slug/volunteers/approve/:approval_link' => [
    'volunteers\Controller',
    'approveVolunteer',
  ],
  'get /organizations/:slug/volunteers/reject/:approval_link' => [
    'volunteers\Controller',
    'rejectVolunteer',
  ],
  'get /organizations/:slug/hours/approve/:approval_link' => [
    'volunteers\Controller',
    'approveHours',
  ],
  'get /organizations/:slug/hours/reject/:approval_link' => [
    'volunteers\Controller',
    'rejectHours',
  ],

  /*
   * Volunteer Hub
   */

  'get /organizations/:slug' => [
    'volunteers\Controller',
    'volunteerHub',
  ],
  'post /organizations/:slug/volunteers' => [
    'volunteers\Controller',
    'joinOrganization',
  ],
  'delete /organizations/:slug/volunteers' => [
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
  'get /organizations/:slug/admin' => [
    'volunteers\Administration',
    'adminIndex',
  ],

  // volunteers
  'get /organizations/:slug/admin/volunteers' => [
    'volunteers\Administration',
    'adminVolunteersBrowse',
  ],
  'get /organizations/:slug/admin/groups' => [
    'volunteers\Administration',
    'adminVolunteersBrowse',
  ],
  'get /organizations/:slug/admin/volunteers/add' => [
    'volunteers\Administration',
    'adminAddVolunteerForm',
  ],
  'post /organizations/:slug/admin/volunteers' => [
    'volunteers\Administration',
    'adminAddVolunteer',
  ],
  'get /organizations/:slug/admin/volunteers/add/import' => [
    'volunteers\Administration',
    'adminAddVolunteerImportForm',
  ],
  'get /organizations/:slug/admin/volunteers/add/group' => [
    'volunteers\Administration',
    'adminAddVolunteerGroupForm',
  ],
  'get /organizations/:slug/admin/volunteers/:id' => [
    'volunteers\Administration',
    'adminVolunteersView',
  ],
  'get /organizations/:slug/admin/groups/:id' => [
    'volunteers\Administration',
    'adminVolunteersViewGroup',
  ],
  'post /organizations/:slug/admin/volunteers/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'post /organizations/:slug/admin/groups/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'delete /organizations/:slug/admin/volunteers/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],
  'delete /organizations/:slug/admin/groups/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],

  // hours
  'get /organizations/:slug/admin/hours/add' => [
    'volunteers\Administration',
    'adminRecordHoursStep1',
  ],
  'get /organizations/:slug/admin/hours/add/2' => [
    'volunteers\Administration',
    'adminRecordHoursStep2',
  ],
  'post /organizations/:slug/admin/hours/add' => [
    'volunteers\Administration',
    'adminRecordHoursStep3',
  ],
  'post /organizations/:slug/admin/hours/add/confirm' => [
    'volunteers\Administration',
    'adminRecordHoursStep4',
  ],
  'get /organizations/:slug/admin/hours' => [
    'volunteers\Administration',
    'adminHoursBrowse',
  ],
  'get /organizations/:slug/admin/hours/:id' => [
    'volunteers\Administration',
    'adminHoursView',
  ],
  'post /organizations/:slug/admin/hours/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'delete /organizations/:slug/admin/hours/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],

  // places
  'get /organizations/:slug/admin/places' => [
    'volunteers\Administration',
    'adminPlacesBrowse',
  ],
  'get /organizations/:slug/admin/places/add' => [
    'volunteers\Administration',
    'adminPlacesAddForm',
  ],
  'post /organizations/:slug/admin/places' => [
    'volunteers\Administration',
    'adminPlacesAdd',
  ],
  'get /organizations/:slug/admin/places/:id' => [
    'volunteers\Administration',
    'adminPlacesView',
  ],
  'get /organizations/:slug/admin/places/:id/edit' => [
    'volunteers\Administration',
    'adminPlacesEditForm',
  ],
  'post /organizations/:slug/admin/places/:id' => [
    'volunteers\Administration',
    'adminModelEdit',
  ],
  'delete /organizations/:slug/admin/places/:id' => [
    'volunteers\Administration',
    'adminModelDelete',
  ],

  // reports
  'get /organizations/:slug/admin/reports' => [
    'volunteers\Administration',
    'adminReports',
  ],
]);
