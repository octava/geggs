<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Plugin\CheckoutPlugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckoutCommand
 * @package Octava\GeggsBundle\Command
 */
class CheckoutCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('checkout')
            ->addArgument('branch', InputArgument::REQUIRED, 'Branch name')
//            ->addOption(
//                'new-branch',
//                'b|B',
//                InputOption::VALUE_NONE,
//                'Specifying -b causes a new branch to be created as if git-branch(1) were called and then checked out.'
//            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->setDescription('Checkout a branch or paths to the working tree');
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

        $pushPlugin = new CheckoutPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $pushPlugin->execute($list);

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
