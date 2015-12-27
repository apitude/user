<?php
namespace Apitude\User\Commands;


use Apitude\Core\Application;
use Apitude\Core\Commands\BaseCommand;
use Apitude\User\UserService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends BaseCommand
{
    public function configure()
    {
        $this->setName('user:create');
        $this->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('roles', InputArgument::IS_ARRAY|InputArgument::REQUIRED);
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Application $app */
        $app = $this->getSilexApplication();

        /** @var UserService $userService */
        $userService = $app[UserService::class];

        $user = $userService->create(
            $input->getArgument('username'),
            $input->getArgument('email'),
            $input->getArgument('password')
        );

        $output->writeln('Created user with id: '.$user->getId());
    }
}
