<?php
namespace Octava\GeggsBundle\Command;

use Monolog\Processor\MemoryPeakUsageProcessor;
use Octava\GeggsBundle\Helper\AbstractGitCommandHelper;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Octava\GeggsBundle\Model\RepositoryModel;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 * @package Octava\GeggsBundle\Command
 */
class StatusCommand extends AbstractGitCommandHelper
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Git status');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger($this->getName());
        $logger->pushHandler(new ConsoleHandler($output));
        $logger->pushProcessor(new MemoryPeakUsageProcessor());
        $logger->debug('Start', ['command_name' => $this->getName()]);

        $io = new SymfonyStyle($input, $output);
        $io->writeln('');
        $config = $this->getContainer()->get('octava_geggs.config');

        $factory = new RepositoryFactory($config, $logger);
        $list = $factory->buildRepositoryModelList();

        $hasChanges = false;
        $projectBranch = $list->getProjectModel()->getBranch();
        foreach ($list->getAll() as $item) {
            $status = $item->getRawStatus();
            if (!empty($status)) {
                $branch = $item->getBranch();
                if ($item->getType() === RepositoryModel::TYPE_ROOT) {
                    $io->writeln(
                        sprintf('<info>project repository</info> <question>[%s]</question>', $branch)
                    );
                } else {

                    if ($projectBranch === $branch) {
                        $io->writeln(
                            sprintf('<info>%s</info> <question>[%s]</question>', $item->getPath(), $branch)
                        );
                    } else {
                        $io->writeln(
                            sprintf(
                                '<info>%s</info> <error>[%s -> %s]</error>',
                                $item->getPath(),
                                $branch,
                                $projectBranch
                            )
                        );
                    }
                }
                $io->writeln($status);
                $io->writeln('');
                $hasChanges = true;
            }
        }
        if (!$hasChanges) {
            $io->writeln('<comment>nothing to commit</comment>');
        }

        $logger->debug('Finish', ['command_name' => $this->getName()]);
    }
}
