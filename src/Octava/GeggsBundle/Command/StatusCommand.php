<?php
namespace Octava\GeggsBundle\Command;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Show the working tree status');
    }
}
