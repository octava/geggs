<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class PushCommand
 * @package Octava\GeggsBundle\Command
 */
class PushCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('push')
            ->setDescription('Update remote refs along with associated objects')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->addOption('only-vendor', null, InputOption::VALUE_NONE, 'Push only vendors');
    }
}
