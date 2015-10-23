<?php
namespace Apitude\User\OAuth;

use Apitude\User\OAuth\Controller\OauthController;
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
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/authorize', OauthController::class.'::authorizeGet');
        $controllers->post('/authorize', OauthController::class.'::authorizePost');
        $controllers->get('/access_token', OauthController::class.'::accessToken');
        $controllers->get('/token', OauthController::class.'::tokenInfo');

        return $controllers;
    }
}
