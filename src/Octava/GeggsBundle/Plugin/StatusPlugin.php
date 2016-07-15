<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ProgressBarHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * Class StatusPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class StatusPlugin extends AbstractPlugin
{
    const MASTER_BRANCH = 'master';
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $progressBar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressBar->create($repositories->count());

        $hasChanges = false;
        $projectBranch = $repositories->getProjectModel()->getBranch();
        $result = [];
        foreach ($repositories->getAll() as $model) {
            $progressBar->advance('Status of ' . ($model->getPath() ?: 'project repository'));

            $branch = $model->getBranch();
            $differentBranch = $projectBranch != $branch && ('master' == $projectBranch || ('master' != $projectBranch && 'master' != $branch));
            $path = $model->getType() === RepositoryModel::TYPE_ROOT ? 'project repository' : $model->getPath();

            /**
             * Check full state of repo
             * Also check branch of repo (if it is not according to project branch - show it)
             */
            $modelHasChanges = $model->hasChanges() || $model->hasCommits() || $differentBranch;
            $hasChanges = $hasChanges || $modelHasChanges;

            if ($modelHasChanges) {
                $result[$path] = ['path' => null, 'branch' => null, 'hasCommits' => null, 'hasChanges' => null, 'hasConflicts' => null];
                $result[$path]['path'] = sprintf('<info>%s</info> ', $path);

                if ($projectBranch == $branch) {
                    $result[$path]['branch'] = sprintf('<question>[%s]</question>', $branch);
                } else {
                    if (self::MASTER_BRANCH == $projectBranch) {
                        if ($projectBranch != $branch) {
                            $result[$path]['branch'] = sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch);
                        }
                    } else {
                        if (self::MASTER_BRANCH == $branch) {
                            if ($model->hasChanges()) {
                                $result[$path]['branch'] = sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch);
                            } else {
                                $result[$path]['branch'] = sprintf('<error>[%s]</error>', $branch);
                            }
                        } else {
                            if ($projectBranch != $branch) {
                                if ($model->hasChanges()) {
                                    $result[$path]['branch'] = sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch);
                                } else {
                                    $result[$path]['branch'] = sprintf('<error>[%s]</error>', $branch);
                                }
                            }
                        }
                    }
                }

                if ($model->hasCommits()) {
                    $result[$path]['hasCommits'] = ' <comment>(has unpushed commits)</comment>';
                }

                if ($model->hasConflicts()) {
                    $result[$path]['hasConflicts'] = ' <error>(has conflicts)</error>';
                }

                if ($model->hasChanges()) {
                    $result[$path]['hasChanges'] = $model->getRawStatus();
                }
            }
        }

        $progressBar->finish();

        if (!$hasChanges) {
            $this->getSymfonyStyle()->writeln('<comment>no changes</comment>');
        } else {
            foreach ($result as $path => $item) {
                $this->getSymfonyStyle()->write($item['path']);
                $this->getSymfonyStyle()->write($item['branch']);
                if (!$item['hasCommits'] && !$item['hasChanges']) {
                    $this->getSymfonyStyle()->writeln('');
                } else {
                    if ($item['hasCommits']) {
                        $this->getSymfonyStyle()->write($item['hasCommits']);
                    }
                    if ($item['hasConflicts']) {
                        $this->getSymfonyStyle()->write($item['hasConflicts']);
                    }
                    $this->getSymfonyStyle()->writeln('');

                    if ($item['hasChanges']) {
                        $this->getSymfonyStyle()->write($item['hasChanges']);
                        $this->getSymfonyStyle()->writeln('');
                    }
                }
                $this->getSymfonyStyle()->writeln('');
            }
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
