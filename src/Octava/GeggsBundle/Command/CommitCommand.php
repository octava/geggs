<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Helper\AbstractGitCommandHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class CommitCommand extends AbstractGitCommandHelper
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
        $io->comment('git commit with flag --all');

        $config = $this->getContainer()->get('octava_geggs.config');

        $arguments = ['commit', '--all'];
        if ($input->getOption('no-verify')) {
            $arguments[] = '--no-verify';
        }
        foreach ($input->getOption('message') as $message) {
            $arguments[] = '-m';
            $arguments[] = $message;
        }

        $cmd = $this->buildCommand($config->getMainDir(), $arguments);
        $io->section('Main directory');
        $io->comment('> '.$config->getMainDir());
        $this->runCommand($cmd, $io);

        $io->section('vendors');
        foreach ($config->getVendorDirs() as $dir) {
            $io->comment('> '.$config->makePathRelative($dir));
            $cmd = $this->buildCommand($dir, $arguments);
            $this->runCommand($cmd, $io);
        }
    }
}
