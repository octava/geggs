<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommitCommand
 * @package Octava\GeggsBundle\Command
 */
class CommitCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('commit')
            ->setAliases(['ci'])
            ->setDescription('Record changes to the repository')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Try operation but make no changes')
            ->addOption('no-verify', null, InputOption::VALUE_NONE, 'To skip commit checks')
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Use the given <message> as the commit message');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $comment = $input->getOption('message');
        if (empty($comment)) {
            $comment = trim(
                $this->getSymfonyStyle()->ask(
                    'Enter comment, please',
                    null,
                    function ($answer) {
                        $answer = trim($answer);
                        if (empty($answer)) {
                            throw new \RuntimeException('Empty comment');
                        }

                        return $answer;
                    }
                )
            );
            $input->setOption('message', $comment);
        }

        parent::interact($input, $output);
    }
}
