<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Helper\AbstractGitCommandHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class StatusCommand extends AbstractGitCommandHelper
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Git status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $config = $this->getContainer()->get('octava_geggs.config');

        $cmd = $this->buildCommand($config->getMainDir(), ['status']);
        $io->section('Main directory');
        $io->note($config->getMainDir());
        $this->runCommand($cmd, $io);

        $io->section('vendors');
        foreach ($config->getVendorDirs() as $dir) {
            $io->note($config->makePathRelative($dir));
            $cmd = $this->buildCommand($dir, ['status']);
            $this->runCommand($cmd, $io);
        }
    }
}
