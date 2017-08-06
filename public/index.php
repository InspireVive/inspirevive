<?php

use Infuse\Application;
use Infuse\Auth\AuthMiddleware;
use Infuse\Middleware\SessionMiddleware;
use Infuse\Request;

define('INFUSE_BASE_DIR', dirname(__DIR__));
set_include_path(get_include_path().PATH_SEPARATOR.INFUSE_BASE_DIR);

require_once 'vendor/autoload.php';
include 'assets/constants.php';

$config = @include 'config.php';
$app = new Application($config, $config['app']['environment']);

// build the request
$req = Request::createFromGlobals();

// set up middleware
$session = new SessionMiddleware();
$session->setApp($app);
$app->middleware($session);

$auth = new AuthMiddleware();
$auth->setApp($app);
$app->middleware($auth);

// NOTE the CSRF middleware must go after the Auth middleware
// because any time a different user is signed in then that will
// destroy the previous session. If the user was not signed in
// then a guest session will be started, and kill any previously
// generated CSRF tokens. Thus, the CSRF token must be generated
// after the Auth middleware.
$app->middleware($app['csrf']);

// build a response and send it
$app->handleRequest($req)->send();