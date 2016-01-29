<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class StatusPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class StatusPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $hasChanges = false;
        $projectBranch = $repositories->getProjectModel()->getBranch();
        foreach ($repositories->getAll() as $item) {
            $status = $item->getRawStatus();
            if (!empty($status)) {
                $branch = $item->getBranch();
                if ($item->getType() === RepositoryModel::TYPE_ROOT) {
                    $this->io->writeln(
                        sprintf('<info>project repository</info> <question>[%s]</question>', $branch)
                    );
                } else {

                    if ($projectBranch === $branch) {
                        $this->io->writeln(
                            sprintf('<info>%s</info> <question>[%s]</question>', $item->getPath(), $branch)
                        );
                    } else {
                        $this->io->writeln(
                            sprintf(
                                '<info>%s</info> <error>[%s -> %s]</error>',
                                $item->getPath(),
                                $branch,
                                $projectBranch
                            )
                        );
                    }
                }
                $this->io->writeln($status);
                $this->io->writeln('');
                $hasChanges = true;
            }
        }
        if (!$hasChanges) {
            $this->io->writeln('<comment>nothing to commit</comment>');
        }
    }
}
