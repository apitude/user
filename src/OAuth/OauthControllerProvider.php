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
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'oauth.controller::signinRedirect');
        $controllers->get('/signin', 'oauth.controller::signinGet');
        $controllers->post('/signin', 'oauth.controller::signinPost');
        $controllers->post('/authorize', 'oauth.controller::authorize');
        $controllers->get('/access_token', 'oauth.controller::accessToken');
        $controllers->post('/access_token', 'oauth.controller::accessToken');
        $controllers->get('/token', 'oauth.controller::tokenInfo');

        return $controllers;
    }
}
