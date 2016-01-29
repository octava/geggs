<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Plugin\PullPlugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PullCommand
 * @package Octava\GeggsBundle\Command
 */
class PullCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pull')
            ->setDescription('Fetch from and integrate with another repository or a local branch')
            ->addArgument('remote-branch', InputArgument::OPTIONAL, 'Name of a branch in the remote repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $list = $this->getRepositoryModelList();

        $pushPlugin = new PullPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $pushPlugin->execute($list);

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
