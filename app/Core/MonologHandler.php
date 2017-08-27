<?php

namespace App\Core;

use Monolog\Handler\ErrorLogHandler;

class MonologHandler
{
    function __construct($app)
    {
        $handler = new ErrorLogHandler();
        $app['logger']->pushHandler($handler);
    }

    function __invoke()
    {
        return $this;
    }
}