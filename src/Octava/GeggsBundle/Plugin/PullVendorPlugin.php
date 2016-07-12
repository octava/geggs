<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ParallelProcess;
use Octava\GeggsBundle\Helper\ProgressBarHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PushPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PullVendorPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);
        $parallelProcess = new ParallelProcess($this->getSymfonyStyle());

        $remoteBranch = null;
        if ($this->getInput()->hasArgument('remote-branch')) {
            $remoteBranch = $this->getInput()->getArgument('remote-branch');
        }
        /** @var RepositoryModel[] $list */
        $list = $repositories->getVendorModels();

        $progressBar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressBar->create(count($list));

        $model = null;
        foreach ($list as $model) {
            $progressBar->advance('Fetch status of '.($model->getPath() ?: 'project repository'));

            $currentBranch = $model->getBranch();

            $hasCommits = $model->hasCommits();

            $needPull = false;
            if ($currentBranch !== $remoteBranch
                || ('master' !== $currentBranch && 'master' !== $remoteBranch)
                || $hasCommits
            ) {
                $needPull = true;
            }

            if ($needPull) {
                $parallelProcess->add(
                    $model->getProvider()->buildCommand('fetch', []),
                    $model->getAbsolutePath(),
                    $this->isDryRun(),
                    false
                );

                if ($model->getProvider()->hasRemoteBranch($currentBranch)) {
                    $parallelProcess->add(
                        $model->getProvider()->buildCommand('pull', ['origin', $currentBranch]),
                        $model->getAbsolutePath(),
                        $this->isDryRun(),
                        false
                    );
                }

                $needMerge = !empty($remoteBranch) && $remoteBranch != $currentBranch;

                if ($needMerge && $model->getProvider()->hasRemoteBranch($remoteBranch)) {
                    $parallelProcess->add(
                        $model->getProvider()->buildCommand('pull', ['origin', $remoteBranch]),
                        $model->getAbsolutePath(),
                        $this->isDryRun(),
                        false
                    );
                }

                if ($needMerge
                    && !$model->hasConflicts()
                    && $model->getProvider()->hasLocalBranch($remoteBranch)
                ) {
                    $parallelProcess->add(
                        $model->getProvider()->buildCommand('merge', [$remoteBranch]),
                        $model->getAbsolutePath(),
                        $this->isDryRun(),
                        true
                    );
                }
            } else {
                $this->getLogger()->debug(
                    'Skipped',
                    [
                        $model->getPackageName(),
                        'current' => $currentBranch,
                        'remote' => $remoteBranch,
                        'hasCommits' => $hasCommits,
                    ]
                );
            }
        }
        $progressBar->finish();

        $parallelProcess->run();

        $hasConflict = false;
        foreach ($list as $model) {
            if ($model->hasConflicts()) {
                $this->getSymfonyStyle()->write($model->getPackageName());
                $this->getSymfonyStyle()->error($model->getConflicts());

                $hasConflict = true;
            }
        }

        if ($hasConflict) {
            $this->getSymfonyStyle()->note('You should solve all conflict');
            $this->stopPropagation();
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
