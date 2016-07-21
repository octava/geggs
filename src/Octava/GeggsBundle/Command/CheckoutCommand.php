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
            ->setAliases(['co'])
            ->addArgument('branch', InputArgument::REQUIRED, 'Branch name')
            ->addOption('repo', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify vendors for checkout')
            ->addOption('no-vendors', null, InputOption::VALUE_NONE, 'Checkout without vendors')
            ->setDescription('Checkout a branch or paths to the working tree');
    }
}
