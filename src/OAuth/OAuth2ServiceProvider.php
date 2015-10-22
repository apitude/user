<?php
namespace Apitude\User\OAuth;

use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\OAuth\Storage;
use League\OAuth2\Server\AuthorizationServer;
use Silex\Application;
use Silex\ServiceProviderInterface;

class OAuth2ServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $services = [
        Storage\AccessTokenStorage::class,
        Storage\AuthCodeStorage::class,
        Storage\ClientStorage::class,
        Storage\RefreshTokenStorage::class,
        Storage\ScopeStorage::class,
        Storage\SessionStorage::class,
    ];

    public function __construct() {
        $this->entityFolders['Apitest\User\OAuth\Entities'] = realpath(__DIR__.'/Entities');
    }

    public function register(Application $app) {
        parent::register($app);

        $app[AuthorizationServer::class] = $app->share(function() use($app) {
            return (new AuthorizationServer())
                ->setAccessTokenStorage($app[Storage\AccessTokenStorage::class])
                ->setSessionStorage($app[Storage\SessionStorage::class])
                ->setRefreshTokenStorage($app[Storage\RefreshTokenStorage::class])
                ->setClientStorage($app[Storage\ClientStorage::class])
                ->setScopeStorage($app[Storage\ScopeStorage::class])
                ->setAuthCodeStorage($app[Storage\AuthCodeStorage::class]);
        });
    }
}
