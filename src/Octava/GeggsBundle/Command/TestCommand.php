<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Helper\RepositoryFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class TestCommand
 * @package Octava\GeggsBundle\Command
 */
class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Test command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $factory = new RepositoryFactory($this->getContainer()->get('octava_geggs.config'));
        $list = $factory->buildRepositoryModelList();

        $rows = [];
        $dump = [];
        foreach ($list as $item) {
            $dump = $item->dump();
            $rows[] = array_values($dump);
        }

        $io->table(array_keys($dump), $rows);
    }
}
