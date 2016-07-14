<?php
namespace Octava\GeggsBundle\Command;

class UpdateComposerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('update-composer')
            ->setAliases(['uc'])
            ->setDescription('Update composer json and lock');
    }
}
