<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputOption;

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
            ->addOption('no-verify', null, InputOption::VALUE_NONE, 'To skip commit checks')
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Use the given <message> as the commit message');
    }
}
