<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Plugin\BranchPlugin;
use Octava\GeggsBundle\Plugin\CommitProjectPlugin;
use Octava\GeggsBundle\Plugin\CommitVendorPlugin;
use Octava\GeggsBundle\Plugin\ComposerPlugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommitCommand
 * @package Octava\GeggsBundle\Command
 */
class CommitCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('commit')
            ->setDescription('Record changes to the repository')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Use the given <message> as the commit message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $list = $this->getRepositoryModelList();

        $branchPlugin = new BranchPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $branchPlugin->execute($list);

        $commitVendorPlugin = new CommitVendorPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $commitVendorPlugin->execute($list);

        $composerPlugin = new ComposerPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $composerPlugin->execute($list);

        $commitProjectPlugin = new CommitProjectPlugin(
            $this->getConfig(),
            $this->getSymfonyStyle(),
            $this->getLogger()
        );
        $commitProjectPlugin->execute($list);

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
