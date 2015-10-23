<?php
namespace Apitude\User\Commands;


use Apitude\Core\Application;
use Apitude\Core\Commands\BaseCommand;
use Apitude\User\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('user:create');
        $this->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('roles', InputArgument::IS_ARRAY|InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Application $app */
        $app = $this->getSilexApplication();

        /** @var EntityManagerInterface $em */
        $em = $app['orm.em'];

        $user = (new User())
            ->setUsername($input->getArgument('username'))
            ->setPassword($input->getArgument('password'))
            ->setRoles($input->getArgument('roles'));

        $em->persist($user);
        $em->flush();

        $output->writeln('Created user with id: '.$user->getId());
    }

}