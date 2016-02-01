<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitCommand
 * @package Octava\GeggsBundle\Command
 */
class InitCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Init geggs environment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $exitCode = 50;
        $configDir = getcwd().'/.geggs';

        $this->getSymfonyStyle()->writeln('Initiating managing process for application with <bold>Geggs</bold>');

        // Check if there is already a config dir
        if (file_exists($configDir)) {
            $this->getSymfonyStyle()->warning('Already exists <bold>.geggs</bold> directory.');
        } else {
            $results = [];
            $results[] = mkdir($configDir);
            $results[] = copy($this->getApplication()->getRootDir(), '');

            if (!in_array(false, $results)) {
                $this->getSymfonyStyle()->success(
                    'The configuration for <bold>Magallanes</bold> has been generated at <blue>.geggs</blue> directory.'
                );
                $this->getSymfonyStyle()->writeln('<bold>Please!! Review and adjust the configuration.</bold>');
                $exitCode = 0;
            } else {
                $this->getSymfonyStyle()->warning('Unable to generate the configuration.');
            }

            return $exitCode;
        }

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
