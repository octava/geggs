<?php
namespace Octava\GeggsBundle\Command;

use Monolog\Processor\MemoryPeakUsageProcessor;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Octava\GeggsBundle\Plugin\ComposerPlugin;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;
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
        $logger = new Logger($this->getName());
        $logger->pushHandler(new ConsoleHandler($output));
        $logger->pushProcessor(new MemoryPeakUsageProcessor());

        $logger->debug('Start', ['command_name' => $this->getName()]);

        $io = new SymfonyStyle($input, $output);

        $config = $this->getContainer()->get('octava_geggs.config');
        $factory = new RepositoryFactory($config, $logger);
        $repositories = $factory->buildRepositoryModelList();

        $rows = [];
        $dump = [];
        foreach ($repositories->getAll() as $item) {
            $dump = $item->dump();
            $rows[] = array_values($dump);
        }

        $io->table(array_keys($dump), $rows);

        $plugin = new ComposerPlugin($config, $io, $logger);
        $plugin->execute($repositories);
    }
}
