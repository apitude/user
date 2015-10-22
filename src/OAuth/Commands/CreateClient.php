<?php
namespace Apitude\User\OAuth\Commands;

use Apitude\Core\Commands\BaseCommand;
use Apitude\User\OAuth\Entities\OauthClient;
use Apitude\User\OAuth\Entities\OauthClientRedirectUri;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClient extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('oauth:client:create');
        $this->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('secret', InputArgument::REQUIRED)
            ->addArgument('redirect_uri', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        /** @var EntityManagerInterface $em */
        $em = $app['orm.em'];

        $client = (new OauthClient())
            ->setName($input->getArgument('name'))
            ->setSecret($input->getArgument('secret'));
        $em->persist($client);
        $em->flush();

        $redirect = (new OauthClientRedirectUri())
            ->setClientId($client->getId())
            ->setRedirectUri($input->getArgument('redirect_uri'));

        $em->persist($redirect);
        $em->flush();

        $output->writeln('Created oauth client');
    }
}
