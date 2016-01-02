<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

// wkhtmltopdf
// load the command from the environment - useful for CI builds
if ($cmd = getenv('WKHTMLTOPDF_CMD')) {
    define('WKHTMLTOPDF_CMD', $cmd);
} else {
	define('WKHTMLTOPDF_CMD', 'wkhtmltopdf --margin-top 0.5cm --margin-left 0.5cm --margin-right 0.5cm --margin-bottom 2cm -s letter --encoding utf8 -q - -');
}

define('ORGANIZATION_ROLE_NONE', -1);
define('ORGANIZATION_ROLE_AWAITING_APPROVAL', 0);
define('ORGANIZATION_ROLE_VOLUNTEER', 1);
define('ORGANIZATION_ROLE_ADMIN', 2);

define('VOLUNTEER_PLACE_INTERNAL', 0);
define('VOLUNTEER_PLACE_EXTERNAL', 1);

// errors
define('ERROR_INVALID_EVENT_DATES', 'invalid_event_dates');
define('ERROR_NOT_VOLUNTEER', 'must_be_volunteer');
define('ERROR_VOLUNTEER_PLACE_NAME_TAKEN', 'place_name_taken');
