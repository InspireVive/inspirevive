<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

require_once 'vendor/autoload.php';

$phinxConfig = [
    'paths' => [],
    'environments' => [
        'default_migration_table' => 'Migrations',
        'default_database' => 'app',],];

$appConfig = @include 'config.php';

// determine the module's path
$module = getenv('PHINX_APP_MODULE');
if ($module) {
    // determine module directory
    $controller = 'app\\'.$module.'\Controller';

    if (class_exists($controller)) {
        $reflection = new ReflectionClass($controller);
        $modDir = dirname($reflection->getFileName()).'/migrations';
        $phinxConfig['paths']['migrations'] = $modDir;
    }
}

// generate environment from config
$environment = $appConfig['database'];
$environment['adapter'] = $environment['type'];
unset($environment['type']);
$environment['pass'] = $environment['password'];
unset($environment['password']);

$phinxConfig['environments']['app'] = $environment;

return $phinxConfig;
