<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Pages;

use Infuse\HasApp;
use Infuse\View;

class Controller
{
    use HasApp;

    public function index($req, $res)
    {
        if ($this->app['user']->isSignedIn()) {
            $res->redirect('/profile');
        } else {
            $res->redirect('/login');
        }
    }

    public function faqs($req, $res)
    {
        return new View('faqs', [
            'title' => 'FAQs',
        ]);
    }

    public function reportingHours($req, $res)
    {
        return new View('help/reporting-hours', [
            'title' => 'Reporting Volunteer Hours',
        ]);
    }
}
