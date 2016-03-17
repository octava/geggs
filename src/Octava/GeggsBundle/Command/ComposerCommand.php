<?php
namespace Octava\GeggsBundle\Command;

class ComposerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('composer')
            ->setDescription('Record changes to the repository');
    }
}
