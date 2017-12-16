<?php

namespace App\Core;

use Infuse\Application;

class TwigLoader
{
    function __construct(Application $app)
    {
        $twig = $app['view_engine']->twig();
        $base = INFUSE_BASE_DIR.'/app/';
        $loader = $twig->getLoader();
        $loader->addPath($base.'Admin/views', 'admin');
        $loader->addPath($base.'Reports/views', 'reports');
        $loader->addPath($base.'Users/views', 'users');
        $loader->addPath($base.'Volunteers/views', 'volunteers');
    }
}