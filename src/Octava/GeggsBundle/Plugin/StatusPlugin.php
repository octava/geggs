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
        foreach ($repositories->getAll() as $model) {
            $branch = $model->getBranch();
            $status = $model->getRawStatus();
            $hasCommits = $model->hasCommits();
            $path = $model->getPath();
            $modelHasChanges = !empty($status) || $hasCommits;
            $hasChanges = $hasChanges || $modelHasChanges;

            if ($modelHasChanges) {
                if ($model->getType() === RepositoryModel::TYPE_ROOT) {
                    $path = 'project repository';
                }
                $this->getSymfonyStyle()->write(sprintf('<info>%s</info> ', $path));

                if ($projectBranch !== $branch) {
                    $this->getSymfonyStyle()->write(sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch));
                } else {
                    $this->getSymfonyStyle()->write(sprintf('<question>[%s]</question>', $branch));
                }

                if ($hasCommits) {
                    $this->getSymfonyStyle()->write(' <comment>(has unpushed commits)</comment>');
                }
                $this->getSymfonyStyle()->writeln('');
            }

            if ($modelHasChanges) {
                $this->getSymfonyStyle()->writeln('');
            }
        }
        if (!$hasChanges) {
            $this->getSymfonyStyle()->writeln('<comment>nothing to commit</comment>');
        }
    }
}
