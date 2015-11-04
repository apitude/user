<?php
namespace Apitude\User\OAuth;

use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\OAuth\Authentication\OAuth2Authenticator;
use Apitude\User\OAuth\Authentication\OAuth2AuthenticatorInterface;
use Apitude\User\OAuth\Authentication\OAuth2Listener;
use Apitude\User\OAuth\Authentication\OAuth2OptionalListener;
use Apitude\User\OAuth\Authentication\OAuth2Provider;
use Apitude\User\OAuth\Commands\CreateClient;
use Apitude\User\OAuth\Commands\CreateScope;
use Apitude\User\OAuth\Controller\OauthController;
use Apitude\User\OAuth\Storage;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Silex\Application;
use Silex\ServiceProviderInterface;

class OAuth2ServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $commands = [
        CreateClient::class,
        CreateScope::class,
    ];

    protected $services = [
        'oauth.accesstoken-storage' => Storage\AccessTokenStorage::class,
        'oauth.authcode-storage' => Storage\AuthCodeStorage::class,
        'oauth.client-storage' => Storage\ClientStorage::class,
        'oauth.refreshtoken-storage' => Storage\RefreshTokenStorage::class,
        'oauth.scope-storage' => Storage\ScopeStorage::class,
        'oauth.session-storage' => Storage\SessionStorage::class,
        OAuth2Provider::class,
        OAuth2Listener::class,
        OAuth2OptionalListener::class,

        'oauth.authenticator' => OAuth2Authenticator::class,
        'oauth.controller' => OauthController::class,
    ];

    public function __construct() {
        $this->entityFolders['Apitude\User\OAuth\Entities'] = realpath(__DIR__.'/Entities');
    }

    public function register(Application $app) {
        parent::register($app);

        $app[AuthorizationServer::class] = $app->share(function() use($app) {
            /** @var AuthorizationServer $server */
            $server = (new AuthorizationServer())
                ->setAccessTokenStorage($app['oauth.accesstoken-storage'])
                ->setSessionStorage($app['oauth.session-storage'])
                ->setRefreshTokenStorage($app['oauth.refreshtoken-storage'])
                ->setClientStorage($app['oauth.client-storage'])
                ->setScopeStorage($app['oauth.scope-storage'])
                ->setAuthCodeStorage($app['oauth.authcode-storage']);

            // standard auth code grant
            $authCodeGrant = new AuthCodeGrant();
            $server->addGrantType($authCodeGrant);

            // password grant used by our apps
            $passwordGrant = new PasswordGrant();
            $passwordGrant->setVerifyCredentialsCallback(function($username, $password) use ($app) {
                /** @var OAuth2AuthenticatorInterface $auth */
                $auth = $app['oauth.authenticator'];
                $user = $auth->findUser(['username' => $username]);
                if ($user) {
                    return $auth->authenticate($user, [
                        'username' => $username,
                        'password' => $password
                    ]);
                }
                return false;
            });
            $server->addGrantType($passwordGrant);

            $refrehTokenGrant = new RefreshTokenGrant();
            $server->addGrantType($refrehTokenGrant);

            return $server;
        });

        $app[ResourceServer::class] = $app->share(function() use($app) {
            return new ResourceServer(
                $app['oauth.session-storage'],
                $app['oauth.accesstoken-storage'],
                $app['oauth.client-storage'],
                $app['oauth.scope-storage']
            );
        });

        $app['security.authentication_listener.factory.oauth'] = $app->protect(function ($name) use ($app) {
            $app['security.authentication_provider.'.$name.'.oauth'] = $app->share(function ($app) {
                return $app[OAuth2Provider::class];
            });

            $app['security.authentication_listener.'.$name.'.oauth'] = $app->share(function ($app) {
                return $app[OAuth2Listener::class];
            });

            return [
                'security.authentication_provider.'.$name.'.oauth',
                'security.authentication_listener.'.$name.'.oauth',
                null,
                'pre_auth'
            ];
        });

        $app['security.authentication_listener.factory.oauth-optional'] = $app->protect(
            function ($name) use ($app) {
                $app['security.authentication_provider.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                    $provider = new OAuth2Provider();
                    $provider->setContainer($app);
                    return $provider;
                });

                $app['security.authentication_listener.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                    $provider = new OAuth2OptionalListener();
                    $provider->setContainer($app);
                    return $provider;
                });

                return [
                    'security.authentication_provider.'.$name.'.oauth-optional',
                    'security.authentication_listener.'.$name.'.oauth-optional',
                    null,
                    'pre_auth'
                ];
            }
        );
    }

    public function boot(Application $app)
    {
        parent::boot($app);
        $app->mount('/oauth', new OauthControllerProvider());
    }
}
