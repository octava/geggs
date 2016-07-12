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
            $progressBar->advance('Status of '.($model->getPath() ?: 'project repository'));

            $branch = $model->getBranch();
            $status = $model->getRawStatus();
            $hasCommits = $model->hasCommits();
            $path = $model->getPath();
            $modelHasChanges = !empty($status) || $hasCommits || $projectBranch !== $branch;
            $hasChanges = $hasChanges || $modelHasChanges;

            if ($modelHasChanges) {
                $result[$path] = ['path' => null, 'branch' => null, 'hasCommits' => null, 'hasChanges' => null];
                if ($model->getType() === RepositoryModel::TYPE_ROOT) {
                    $path = 'project repository';
                }
                $result[$path] = ['path' => null, 'branch' => null, 'hasCommits' => null, 'hasChanges' => null];
                $result[$path]['path'] = sprintf('<info>%s</info> ', $path);

                if ($projectBranch !== $branch) {
                    $result[$path]['branch'] = sprintf('<error>[%s -> %s]</error>', $branch, $projectBranch);
                } else {
                    $result[$path]['branch'] = sprintf('<question>[%s]</question>', $branch);
                }

                if ($hasCommits) {
                    $result[$path]['hasCommits'] = ' <comment>(has unpushed commits)</comment>';
                }

                if ($status) {
                    $result[$path]['hasChanges'] = $status;
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
                    $this->getSymfonyStyle()->write('<comment>no changes</comment>');
                } else {
                    if ($item['hasCommits']) {
                        $this->getSymfonyStyle()->write($item['hasCommits']);
                    }
                    $this->getSymfonyStyle()->writeln('');

                    if ($item['hasChanges']) {
                        $this->getSymfonyStyle()->write($item['hasChanges']);
                    }
                }
                $this->getSymfonyStyle()->writeln('');
            }
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
