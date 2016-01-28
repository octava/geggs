<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Helper\RepositoryFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            ->setDescription('Fetch from and integrate with another repository or a local branch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->getContainer()->get('octava_geggs.config');
        $factory = new RepositoryFactory($this->getContainer()->get('octava_geggs.config'));
        $list = $factory->buildRepositoryModelList();

        foreach ($list->getAll() as $item) {
            $io->section($item->getPackageName());
            $io->note($item->getPath());

//            $process = $status->run($item->getAbsolutePath());

            $io->writeln($process->getOutput());
        }
    }
}
