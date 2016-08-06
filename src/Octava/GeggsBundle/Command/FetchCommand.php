<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class FetchCommand
 * @package Octava\GeggsBundle\Command
 */
class FetchCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('fetch')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Fetch all remotes')
            ->addOption(
                'prune',
                'p',
                InputOption::VALUE_NONE,
                'After fetching, remove any remote-tracking references that no longer exist on the remote.'
            )
            ->setDescription('Download objects and refs from another repository');
    }
}
