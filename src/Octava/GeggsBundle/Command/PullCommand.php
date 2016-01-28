<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Helper\RepositoryFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class PullCommand
 * @package Octava\GeggsBundle\Command
 */
class PullCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pull')
            ->setDescription('Fetch from and integrate with another repository or a local branch')
            ->addArgument('remote-branch', InputArgument::OPTIONAL, 'Name of a branch in the remote repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger($this->getName());
        $logger->pushHandler(new ConsoleHandler($output));
        $logger->pushProcessor(new MemoryPeakUsageProcessor());
        $logger->debug('Start', ['command_name' => $this->getName()]);

        $io = new SymfonyStyle($input, $output);

        $config = $this->getContainer()->get('octava_geggs.config');
        $factory = new RepositoryFactory($this->getContainer()->get('octava_geggs.config'), $logger);
        $list = $factory->buildRepositoryModelList();

        $logger->debug('Finish', ['command_name' => $this->getName()]);
    }
}
