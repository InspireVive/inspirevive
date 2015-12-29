<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\users;

use app\api\libs\ApiRoute;

class API
{
    use \InjectApp;

    public function currentUser($req, $res)
    {
        $app = $this->app;

        $route = new ApiRoute();
        $route->addParseSteps(['parseRequireJson'])
              ->addQueryStep(function ($route) use ($app) {
                    return $app['user'];
                })
              ->addTransformSteps([
                function (&$result, $route) {
                    $result = [
                        'user' => $result->toArray(),
                    ];
                },
                'transformOutputJson', ]);

        $route->execute($req, $res, $app);
    }

    public function organizations($req, $res)
    {
        $app = $this->app;
        $user = $app['user'];

        $route = new ApiRoute();
        $route->addQueryParams([
                'model' => 'app\volunteers\models\Volunteer',
                'where' => [
                    'organization IN ( SELECT id FROM Organizations WHERE volunteer_organization IS NOT NULL )',
                        'uid' => $user->id(),
                        'role >= '.ORGANIZATION_ROLE_VOLUNTEER, ], ])
              ->addParseSteps([
                'parseRequireJson',
                'parseModelFindAllParameters', ])
              ->addQueryStep('queryModelFindAll')
              ->addTransformSteps([
                'transformModelFindAll',
                'transformOutputJson', ]);

        $route->execute($req, $res);
    }
}
