<?php
namespace Apitude\User\OAuth\Commands;

use Apitude\Core\Commands\BaseCommand;
use Apitude\User\OAuth\Entities\OauthClient;
use Apitude\User\OAuth\Entities\OauthClientRedirectUri;
use Apitude\User\OAuth\Entities\OauthScope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateScope extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('oauth:scope:create');
        $this->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('description', InputArgument::OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        /** @var EntityManagerInterface $em */
        $em = $app['orm.em'];

        $scope = (new OauthScope())
            ->setId($input->getArgument('name'));
        if ($input->getArgument('description')) {
            $scope->setDescription($input->getArgument('description'));
        } else {
            $scope->setDescription('');
        }
        $em->persist($scope);
        $em->flush();

        $output->writeln('Created oauth scope: '. $scope->getId());
    }
}
