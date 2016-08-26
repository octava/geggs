<?php
namespace Project\Geggs\Command;

use Octava\GeggsBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckoutCommand
 * @package Octava\GeggsBundle\Command
 */
class ExampleCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('example')
            ->addArgument('name', InputArgument::REQUIRED, 'Name')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->setDescription('Example command');
    }

    /**
     * @param InputInterface         $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $list = $this->getRepositoryModelList();
        $plugins = $this->getPlugins();

        $this->getSymfonyStyle()->success(sprintf('Hello %s', $input->getArgument('name')));

        foreach ($plugins as $plugin) {
            $plugin->execute($list);
        }

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
