<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return [
  'post /api/auth/token' => [
    'oauth2\Controller',
    'token',
  ],
  'delete /api/auth/token' => [
    'oauth2\Controller',
    'tokenSignOut',
  ],
  'get /api/users/current' => [
    'users\API',
    'currentUser',
  ],
  'get /api/users/current/orgs' => [
    'users\API',
    'organizations',
  ],
  'get /api/organizations/:id/notifications' => [
    'volunteers\API',
    'apiNotifications',
  ],
  'get /api/organizations/:id/dashboard' => [
    'volunteers\API',
    'apiDashboard',
  ],
  'get /api/volunteers' => [
    'volunteers\API',
    'apiVolunteers',
  ],
  'get /api/volunteers/:id/activity' => [
    'volunteers\API',
    'apiVolunteerActivity',
  ],
  'get /api/volunteers/volunteer_hours' => [
    'volunteers\API',
    'apiVolunteerHours',
  ],
  'get /api/volunteers/volunteer_places' => [
    'volunteers\API',
    'apiVolunteerPlaces',
  ],
  'get /api/reports/:organization' => [
    'reports\Controller',
    'makeReport',
  ],
];
