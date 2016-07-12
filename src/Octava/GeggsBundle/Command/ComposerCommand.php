<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputOption;

class ComposerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('composer')
            ->setDescription('Record changes to the repository');
    }
}
