<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Git\Status;
use Octava\GeggsBundle\Helper\AbstractGitCommandHelper;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Symfony\Component\Console\Input\InputInterface;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $config = $this->getContainer()->get('octava_geggs.config');

        $factory = new RepositoryFactory($config);
        $list = $factory->buildRepositoryModelList();

        foreach ($list as $item) {
            $io->section($item->getPackageName());
            $io->note($item->getPath());

            $status = new Status($config);
            $process = $status->run($item->getAbsolutePath());

            $io->writeln($process->getOutput());
        }

    }
}
