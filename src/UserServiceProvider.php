<?php
namespace Apitude\User;

use Apitude\Core\Commands\BaseCommand;
use Apitude\Core\Provider\AbstractServiceProvider;
use Apitude\User\Entities\User;
use Apitude\User\ORM\UserStampSubscriber;
use Apitude\User\Security\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\ServiceProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $services = [
        'user' => UserProvider::class,
    ];

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
        // setup security for cli commands
        if (php_sapi_name() === 'cli') {
            $app['console.configure'][] = function (BaseCommand $command) {
                $command->addOption('user', 'U', InputOption::VALUE_REQUIRED, 'User ID to run command as');
            };
            $app['console.prerun'][] = function (BaseCommand $command, InputInterface $input, OutputInterface $output) {
                if ($input->getOption('user')) {
                    $app = $command->getSilexApplication();
                    /** @var EntityManagerInterface $em */
                    $em = $app['orm.em'];
                    $app['user'] = $em->find(User::class, $input->getOption('user'));
                }
            };
        }

        $app['security.firewalls'] = [];
        $app->register(new SecurityServiceProvider);
    }
}
