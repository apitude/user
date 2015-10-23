<?php
namespace Apitude\User\OAuth\Commands;

use Apitude\Core\Commands\BaseCommand;
use Apitude\User\OAuth\Entities\OauthClient;
use Apitude\User\OAuth\Entities\OauthClientRedirectUri;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClient extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('oauth:client:create');
        $this->addArgument('name', InputArgument::REQUIRED)
            ->addOption('redirect_uri', null, InputOption::VALUE_REQUIRED)
            ->addOption('secret', 's', InputOption::VALUE_REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        /** @var EntityManagerInterface $em */
        $em = $app['orm.em'];

        $client = (new OauthClient())
            ->setName($input->getArgument('name'));
        if ($input->getOption('secret')) {
            $client->setSecret($input->getOption('secret'));
        } else {
            $client->setSecret('');
        }
        $em->persist($client);
        $em->flush();

        if ($input->getOption('redirect_uri')) {
            $redirect = (new OauthClientRedirectUri())
                ->setClientId($client->getId())
                ->setRedirectUri($input->getOption('redirect_uri'));

            $em->persist($redirect);
            $em->flush();
        }

        $output->writeln('Created oauth client: '. $client->getId());
    }
}
