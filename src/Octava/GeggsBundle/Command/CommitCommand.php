<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class CommitCommand extends GitCommand
{
    protected function configure()
    {
        $this
            ->setName('commit')
            ->addOption(
                'message',
                'm',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Use the given <msg> as the commit message. If multiple -m options are given, their values are concatenated as separate paragraphs.'
            )
            ->addOption('no-verify', null, InputOption::VALUE_NONE, 'To skip commit checks')
            ->setDescription('Git commit');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $io->warning('git commit with flag --all');

        $config = $this->getContainer()->get('octava_geggs.config');

        $arguments = ['commit', '--all'];
        if ($input->getOption('no-verify')) {
            $arguments[] = '--no-verify';
        }
        $cmd = $this->buildCommand($config->getMainDir(), $arguments);
        $io->section('Main directory');
        $io->note($config->getMainDir());
        $this->runCommand($cmd, $io);

        $io->section('vendors');
        foreach ($config->getVendorDirs() as $dir) {
            $io->note($config->makePathRelative($dir));
            $cmd = $this->buildCommand($dir, $arguments);
            $this->runCommand($cmd, $io);
        }
    }
}
