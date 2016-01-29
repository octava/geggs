<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Plugin\PushPlugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PushCommand
 * @package Octava\GeggsBundle\Command
 */
class PushCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('push')
            ->setDescription('Update remote refs along with associated objects')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $list = $this->getRepositoryModelList();

        $pushPlugin = new PushPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $pushPlugin->execute($list);

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
