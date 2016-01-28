<?php
namespace Octava\GeggsBundle\Command;

use Monolog\Processor\MemoryPeakUsageProcessor;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Octava\GeggsBundle\Plugin\BranchPlugin;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CommitCommand
 * @package Octava\GeggsBundle\Command
 */
class CommitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('commit')
            ->setDescription('Commit command');
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

        $branchPlugin = new BranchPlugin($config, $io, $logger);
        $branchPlugin->execute($list);

        /** @var RepositoryModel $projectRepository */
        /** @var RepositoryModel[] $vendors */
        list($projectRepository, $vendors) = $this->getRepositories($repositories);

        $logger->debug('Finish', ['command_name' => $this->getName()]);
    }
}
