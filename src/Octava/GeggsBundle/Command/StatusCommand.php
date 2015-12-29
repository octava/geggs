<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->addArgument('pathspec', InputArgument::IS_ARRAY, 'pathspec')
            ->setDescription('Git status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $config = $this->getContainer()->get('octava_geggs.config');
        $paths = $input->getArgument('pathspec');

        $cmd = $this->buildCommand($config->getBin(), $config->getMainDir(), $paths);
        $io->section('Main directory');
        $io->note($config->getMainDir());
        $this->runCommand($cmd, $io);
    }

    /**
     * @param $bin
     * @param $dir
     * @param $paths
     * @return string
     */
    protected function buildCommand($bin, $dir, array $paths = [])
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($bin);
        $builder
            ->add('status');
        foreach ($paths as $path) {
            $builder->add($path);
        }

        $cmd = $builder->getProcess()
            ->getCommandLine();
        $cmd = 'cd '.$dir.' && '.$cmd;

        return $cmd;
    }

    /**
     * @param $cmd
     * @param $io
     */
    protected function runCommand($cmd, SymfonyStyle $io)
    {
        $process = new Process($cmd);
        $process->setTimeout(3600);
        $process->setIdleTimeout(60);
        try {
            $process->mustRun();

            $io->text($process->getOutput());
        } catch (ProcessFailedException $e) {
            $io->error($e->getMessage());
        }
    }
}
