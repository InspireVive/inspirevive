<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

define('INFUSE_BASE_DIR', dirname(__DIR__));
set_include_path(get_include_path().PATH_SEPARATOR.INFUSE_BASE_DIR);

require_once 'vendor/autoload.php';
include 'assets/constants.php';

$config = @include 'config.php';
$app = new App($config);
$app->go();
