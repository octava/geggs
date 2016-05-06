<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputOption;

class ComposerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('composer')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->setDescription('Record changes to the repository');
    }
}
