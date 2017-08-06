<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Infuse\Auth\Console\ResetPasswordLinkCommand;
use Infuse\Console\OptimizeCommand;
use Infuse\Cron\Console\RunScheduledCommand;
use Infuse\Migrations\Console\MigrateCommand;

return [
    'console' => [
        'commands' => [
            // built-in commands
            OptimizeCommand::class,
            // external commands
            ResetPasswordLinkCommand::class,
            RunScheduledCommand::class,
            MigrateCommand::class,
            // app commands
        ]
    ]
];