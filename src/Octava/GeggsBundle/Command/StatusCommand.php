<?php
namespace Octava\GeggsBundle\Command;

use Monolog\Processor\MemoryPeakUsageProcessor;
use Octava\GeggsBundle\Git\Status;
use Octava\GeggsBundle\Helper\AbstractGitCommandHelper;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class StatusCommand extends AbstractGitCommandHelper
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Git status');
    }

    /**
     * @param InputInterface         $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger($this->getName());
        $logger->pushHandler(new ConsoleHandler($output));
        $logger->pushProcessor(new MemoryPeakUsageProcessor());

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $config = $this->getContainer()->get('octava_geggs.config');

        $factory = new RepositoryFactory($config, $logger);
        $list = $factory->buildRepositoryModelList();

        foreach ($list as $item) {
            $io->section($item->getPackageName());
            $io->note($item->getPath());

            $io->writeln($item->getRawStatus());
        }
    }
}
