<?php
namespace Apitude\User\OAuth;

use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\OAuth\Authentication\OAuthToken;
use Apitude\User\OAuth\Authentication\WebPasswordGrant;
use Apitude\User\OAuth\Commands\CreateClient;
use Apitude\User\OAuth\Commands\CreateScope;
use Apitude\User\OAuth\Storage;
use Apitude\User\Security\UserProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\SecurityContext;

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
    }

    public function boot(Application $app)
    {
        parent::boot($app);
        $app->mount('/oauth', new OauthControllerProvider());
    }
}
