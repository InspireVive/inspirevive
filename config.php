<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

// determine environment
$env = getenv('INFUSE_ENV');

// always use test environment for phpunit
if (getenv('PHPUNIT')) {
    $env = 'test';
}

if (!$env) {
    die('INFUSE_ENV not set!');
}

$settings = [];

// include base settings
$pattern = __DIR__.'/config/*.php';
foreach (glob($pattern) as $filename) {
    $section = include $filename;
    $settings = array_replace_recursive($settings, $section);
}

// merge any environment-specific settings
$pattern = __DIR__.'/config/environments/'.$env.'/*.php';
foreach (glob($pattern) as $filename) {
    $section = include $filename;
    $settings = array_replace_recursive($settings, $section);
}

$settings['app']['environment'] = $env;

return $settings;