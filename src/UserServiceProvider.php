<?php
namespace Apitude\User;

use Apitude\Core\Commands\BaseCommand;
use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\Commands\CreateUser;
use Apitude\User\Entities\User;
use Apitude\User\ORM\UserStampSubscriber;
use Apitude\User\Security\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\ServiceProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $commands = [
        CreateUser::class,
    ];

    protected $services = [
        UserProvider::class,
        UserService::class,
        UserStampSubscriber::class,
    ];

    protected $doctrineEventSubscribers = [
        UserStampSubscriber::class,
    ];

    public function __construct()
    {
        $this->entityFolders['Apitude\User\Entities'] = realpath(__DIR__.'/Entities');
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
        parent::register($app);

        // override this in config to allow a different user entity class to be used.
        if (isset($app['config']['user.entity'])) {
            $app['user.entity'] = $app['config']['user.entity'];
        } else {
            $app['user.entity'] = User::class;
        }

        // setup security for cli commands
        if (php_sapi_name() === 'cli') {
            $app['console.configure'] = $app->extend('console.configure', function ($callbacks) {
                $callbacks[] = function (Command $command) {
                    if ($command instanceof BaseCommand) {
                        $command->addOption('user', 'U', InputOption::VALUE_REQUIRED, 'User ID to run command as');
                    }
                };
                return $callbacks;
            });
            $app['console.prerun'] = $app->extend('console.prerun', function ($callbacks) {
                $callbacks[] = function (Command $command, InputInterface $input, OutputInterface $output) {
                    if ($command instanceof BaseCommand && $input->getOption('user')) {
                        $app = $command->getSilexApplication();
                        /** @var EntityManagerInterface $em */
                        $em = $app['orm.em'];
                        $app['user'] = $em->find($app['user.entity'], $input->getOption('user'));
                    }
                };
                return $callbacks;
            });
        }

        $app['security.firewalls'] = [];
        $app->register(new SecurityServiceProvider);
    }
}
