<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Plugin\StatusPlugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Show the working tree status');
    }

    /**
     * @param InputInterface         $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $list = $this->getRepositoryModelList();

        $pushPlugin = new StatusPlugin($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
        $pushPlugin->execute($list);

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
