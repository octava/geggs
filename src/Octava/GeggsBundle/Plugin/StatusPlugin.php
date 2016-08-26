<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ProgressBarHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

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
            $differentBranch = $this->isDifferentBranches($projectBranch, $branch);
            $path = $model->getType() === RepositoryModel::TYPE_ROOT ? 'project repository' : $model->getPath();

            /**
             * Check full state of repo
             * Also check branch of repo (if it is not according to project branch - show it)
             */
            $unpushedCommits = $model->getUnpushedCommits();
            $gitHasUnpushedCommits = $model->hasCommits();
            $gitHasChanges = $model->hasChanges();
            $modelHasChanges = $gitHasChanges || $gitHasUnpushedCommits || $differentBranch;
            $hasChanges = $hasChanges || $modelHasChanges;

            if ($modelHasChanges) {
                $result[$path] = ['path' => null, 'branch' => null, 'hasCommits' => null, 'hasChanges' => null, 'hasConflicts' => null, 'unpushedCommit' => null];
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
                            if ($gitHasChanges) {
                                $result[$path]['branch'] = sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch);
                            } else {
                                $result[$path]['branch'] = sprintf('<error>[%s]</error>', $branch);
                            }
                        } else {
                            if ($projectBranch != $branch) {
                                if ($gitHasChanges) {
                                    $result[$path]['branch'] = sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch);
                                } else {
                                    $result[$path]['branch'] = sprintf('<error>[%s]</error>', $branch);
                                }
                            }
                        }
                    }
                }

                if ($gitHasUnpushedCommits) {
                    $result[$path]['hasCommits'] = ' <comment>(has unpushed commits)</comment>';
                }

                if ($model->hasConflicts()) {
                    $result[$path]['hasConflicts'] = ' <error>(has conflicts)</error>';
                }

                if ($gitHasChanges) {
                    $result[$path]['hasChanges'] = ltrim($model->getRawStatus());
                }

                if ($gitHasUnpushedCommits) {
                    $result[$path]['unpushedCommit'] .= "<comment>Unpushed commits:</comment>\n";
                    foreach ($unpushedCommits as $unpushedCommit) {
                        $result[$path]['unpushedCommit'] .= sprintf("<comment>%s</comment>\n", $unpushedCommit);
                    }
                }
            }
        }
        $progressBar->finish();

        if (!$hasChanges) {
            $this->getSymfonyStyle()->writeln('<comment>no changes</comment>');
        } else {
            $this->displayStatus($result);
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }

    /**
     * @param string $projectBranch
     * @param string $branch
     * @return bool
     */
    protected function isDifferentBranches($projectBranch, $branch)
    {
        $differentBranch = false;
        if ($projectBranch == $branch) {
            if (self::MASTER_BRANCH != $projectBranch) {
                $differentBranch = true;
            }
        } else {
            if (self::MASTER_BRANCH != $branch) {
                $differentBranch = true;
            }
        }

        return $differentBranch;
    }

    /**
     * @param array $data
     */
    protected function displayStatus(array $data)
    {
        foreach ($data as $path => $item) {
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
                if ($item['unpushedCommit']) {
                    $this->getSymfonyStyle()->writeln('');
                    $this->getSymfonyStyle()->write($item['unpushedCommit']);
                }
            }
            $this->getSymfonyStyle()->writeln('');
        }
    }
}
