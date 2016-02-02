<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Class PullCommand
 * @package Octava\GeggsBundle\Command
 */
class PullCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pull')
            ->setDescription('Fetch from and integrate with another repository or a local branch')
            ->addArgument('remote-branch', InputArgument::OPTIONAL, 'Name of a branch in the remote repository');
    }
}
