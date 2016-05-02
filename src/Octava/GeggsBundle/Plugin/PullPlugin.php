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
                $model->getProvider()->buildCommand('pull', ['origin', $currentBranch]),
                $this->isDryRun(),
                false
            );

            if (!empty($remoteBranch) && $remoteBranch != $currentBranch
                && ($model->getProvider()->hasLocalBranch($remoteBranch)
                    || $model->getProvider()->hasLocalBranch($remoteBranch))
            ) {
                $parallelProcess->add(
                    $model->getProvider()->buildCommand('pull', ['origin', $remoteBranch]),
                    $this->isDryRun(),
                    false
                );
            }
        }

        $parallelProcess->run();
    }
}
