<?php
namespace Apitude\User;


use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\ORM\UserStampSubscriber;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\ServiceProviderInterface;

class UserServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $doctrineEventSubscribers = [
        UserStampSubscriber::class,
    ];

    public function __construct()
    {
        $this->services[] = UserStampSubscriber::class;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app->register(new SecurityServiceProvider());
    }
}
