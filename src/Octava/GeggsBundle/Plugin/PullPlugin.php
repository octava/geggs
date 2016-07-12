<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ParallelProcess;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PushPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PullPlugin extends AbstractPlugin
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
        $list = array_reverse($repositories->getAll());

        $model = null;
        foreach ($list as $model) {
            $currentBranch = $model->getBranch();

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
                    true
                );
            }

            $needMerge = !empty($remoteBranch) && $remoteBranch != $currentBranch;

            if ($needMerge && $model->getProvider()->hasRemoteBranch($remoteBranch)) {
                $parallelProcess->add(
                    $model->getProvider()->buildCommand('pull', ['origin', $remoteBranch]),
                    $model->getAbsolutePath(),
                    $this->isDryRun(),
                    true
                );
            }

            if ($needMerge && $model->getProvider()->hasLocalBranch($remoteBranch)) {
                $parallelProcess->add(
                    $model->getProvider()->buildCommand('merge', [$remoteBranch]),
                    $model->getAbsolutePath(),
                    $this->isDryRun(),
                    true
                );
            }
        }

        $parallelProcess->run();

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
