<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

return [
    'phrases' => [
        /* Generic */
        'success' => 'Success!',
        'no_permission' => 'You do not have permission to do that',

        /* Users */
        'user_bad_email' => 'Please enter a valid e-mail address.',
        'user_bad_username' => 'Please enter a valid username.',
        'user_bad_password' => 'Please enter a valid password.',
        'user_login_no_match' => 'We could not find a match for that email address and password.',
        'user_login_banned' => 'Sorry, your account has been banned or disabled.',
        'user_login_temporary' => 'It looks like your account has not been setup yet. Please go to sign up to finish creating your account.',
        'user_login_unverified' => 'You must verify your account with the e-mail that was sent to you before you can log in.',
        'user_forgot_password_success' => '<strong>Success!</strong> Your password has been changed.',
        'user_forgot_email_no_match' => 'We could not find a match for that e-mail address.',
        'user_forgot_expired_invalid' => 'This link has expired or is invalid.',
        'invalid_password' => 'Oops, looks like the password is incorrect.',

        /* Validation */
        'validation_failed' => '{{field_name}} is invalid',
        'required_field_missing' => '{{field_name}} missing',
        'not_unique' => '{{field_name}} has already been used',
        'email_address_banned' => 'This e-mail address has been banned.',
        'user_name_banned' => 'This user name has been banned.',
        'passwords_not_matching' => 'The two passwords do not match.',

        /* Custom */
        'could_not_add_volunteer_email' => 'Unable to add "{{email}}" as a volunteer. Is this a valid e-mail or registered username?',
        'invalid_num_volunteer_hours' => 'Invalid number of hours - must be between 1 and 12. If you are reporting hours volunteered over multiple days then please create a new entry for each day.',
        'invalid_hours_timestamp' => 'Invalid date - cannot report hours that happened in the future.',
        'invalid_volunteer_hour_tags' => 'One or more tags is invalid. Only letters, numbers, and "-" are allowed.',
        'invalid_event_dates' => 'Event start and end dates are not valid',
        'must_be_volunteer' => 'Person must be a volunteer first',
        'place_name_taken' => 'A volunteer place named {{place_name}} already exists. Please use the existing place or choose a different name.',

        /* Pluralizations */
        'volunteer_hours_singular' => 'volunteer hour',
        'volunteer_hours_plural' => 'volunteer hours',
    ],
];
