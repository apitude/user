<?php
namespace Apitude\User\OAuth;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class OauthControllerProvider implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $app->get('/authorize', self::class.'::authorizeGet');
    }
}