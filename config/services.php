<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Infuse\Auth\Services\Auth;
use Infuse\Email\MailerService;
use Infuse\Services\Locale;
use Infuse\Services\QueueDriver;
use Infuse\Services\Redis;
use Infuse\Services\ViewEngine;
use JAQB\Services\ConnectionManager;
use Pulsar\Services\ModelDriver;
use Pulsar\Services\ErrorStack;

return [
    'services' => [
        // infuse
        'redis' => Redis::class,
        'view_engine' => ViewEngine::class,
        'queue_driver' => QueueDriver::class,
        'locale' => Locale::class,

        // pulsar / jaqb
        'database' => ConnectionManager::class,
        'errors' => ErrorStack::class,
        'model_driver' => ModelDriver::class,

        // external modules
        'auth' => Auth::class,
        'mailer' => MailerService::class,
    ],
];