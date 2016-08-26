<?php
namespace Octava\GeggsBundle\Command;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SelfUpdateCommand
 * @package Octava\GeggsBundle\Command
 */
class SelfUpdateCommand extends Command
{
    const MANIFEST_FILE = 'https://raw.githubusercontent.com/octava/geggs/gh-pages/manifest.json';

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates geggs.phar to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $manager->update($this->getApplication()->getVersion(), true);
    }
}
