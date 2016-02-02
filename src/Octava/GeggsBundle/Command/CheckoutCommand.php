<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CheckoutCommand
 * @package Octava\GeggsBundle\Command
 */
class CheckoutCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('checkout')
            ->addArgument('branch', InputArgument::REQUIRED, 'Branch name')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->setDescription('Checkout a branch or paths to the working tree');
    }
}
