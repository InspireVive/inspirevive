<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Core\MonologHandler;
use App\Core\TwigLoader;
use Infuse\Auth\Services\Auth;
use Infuse\Csrf\Csrf;
use Infuse\Csrf\CsrfTokens;
use Infuse\Email\MailerService;
use Infuse\Services\Locale;
use Infuse\Services\QueueDriver;
use Infuse\Services\Redis;
use Infuse\Services\ViewEngine;
use JAQB\Services\ConnectionManager;
use Pulsar\Services\ErrorStack;
use Pulsar\Services\ModelDriver;

return [
    'services' => [
        'monolog_handler' => MonologHandler::class,

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
        'csrf' => Csrf::class,
        'csrf_tokens' => CsrfTokens::class,
        'mailer' => MailerService::class,

        'twig_loader' => TwigLoader::class,
    ],
];