<?php
namespace Octava\Geggs\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 * @package Octava\Geggs\Command
 */
class StatusCommand extends AbstractContainerCommand
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->addArgument('pathspec', InputArgument::IS_ARRAY, 'pathspec');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('git status');

    }
}
