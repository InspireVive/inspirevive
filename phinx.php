<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

$phinxConfig = [
    'environments' => [],
];

$appConfig = @include 'config.php';

$migrationPath = getenv('PHINX_MIGRATION_PATH');
if ($migrationPath) {
    $phinxConfig['paths'] = ['migrations' => $migrationPath];
}

// generate database environment from config
foreach ($appConfig['database'] as $k => $environment) {
    $environment['adapter'] = $environment['type'];
    $environment['pass'] = $environment['password'];
    unset($environment['type']);
    unset($environment['password']);

    $phinxConfig['environments'][$k] = $environment;
}

$phinxConfig['environments']['default_migration_table'] = 'Migrations';
$phinxConfig['environments']['default_database'] = array_keys($appConfig['database'])[0];

return $phinxConfig;
