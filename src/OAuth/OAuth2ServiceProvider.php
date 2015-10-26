<?php
namespace Apitude\User\OAuth;

use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\OAuth\Authentication\OAuth2Listener;
use Apitude\User\OAuth\Authentication\OAuth2OptionalListener;
use Apitude\User\OAuth\Authentication\OAuth2Provider;
use Apitude\User\OAuth\Commands\CreateClient;
use Apitude\User\OAuth\Commands\CreateScope;
use Apitude\User\OAuth\Storage;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
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
        Storage\AccessTokenStorage::class,
        Storage\AuthCodeStorage::class,
        Storage\ClientStorage::class,
        Storage\RefreshTokenStorage::class,
        Storage\ScopeStorage::class,
        Storage\SessionStorage::class,
        OAuth2Provider::class,
        OAuth2Listener::class,
    ];

    public function __construct() {
        $this->entityFolders['Apitude\User\OAuth\Entities'] = realpath(__DIR__.'/Entities');
    }

    public function register(Application $app) {
        parent::register($app);

        $app[AuthorizationServer::class] = $app->share(function() use($app) {
            /** @var AuthorizationServer $server */
            $server = (new AuthorizationServer())
                ->setAccessTokenStorage($app[Storage\AccessTokenStorage::class])
                ->setSessionStorage($app[Storage\SessionStorage::class])
                ->setRefreshTokenStorage($app[Storage\RefreshTokenStorage::class])
                ->setClientStorage($app[Storage\ClientStorage::class])
                ->setScopeStorage($app[Storage\ScopeStorage::class])
                ->setAuthCodeStorage($app[Storage\AuthCodeStorage::class]);

            $authCodeGrant = new AuthCodeGrant();
            $server->addGrantType($authCodeGrant);

            $refrehTokenGrant = new RefreshTokenGrant();
            $server->addGrantType($refrehTokenGrant);

            return $server;
        });

        $app[ResourceServer::class] = $app->share(function() use($app) {
            return new ResourceServer(
                $app[Storage\SessionStorage::class],
                $app[Storage\AccessTokenStorage::class],
                $app[Storage\ClientStorage::class],
                $app[Storage\ScopeStorage::class]
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
                    return new OAuth2Provider(
                        $app['user.mapper'],
                        $app['user-role-pivot.mapper'],
                        $app['oauth_server']
                    );
                });

                $app['security.authentication_listener.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                    return new OAuth2OptionalListener($app['security'], $app['security.authentication_manager']);
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
