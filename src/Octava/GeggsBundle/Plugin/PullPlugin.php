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

        $remoteBranch = $this->getInput()->getArgument('remote-branch');
        /** @var RepositoryModel[] $list */
        $list = array_reverse($repositories->getAll());

        $model = null;
        foreach ($list as $model) {
            $currentBranch = $model->getBranch();

//            $this->getSymfonyStyle()->writeln(
//                sprintf(
//                    '%s pulled from %s',
//                    $model->getPath() ? $model->getPath() : 'main',
//                    $currentBranch
//                )
//            );

            $parallelProcess->add(
                $model->getProvider()->buildCommand('pull', ['origin', $currentBranch]),
                $this->isDryRun(),
                false
            );
//            if (!empty($remoteBranch) && $remoteBranch != $currentBranch) {
//                $parallelProcess->add(
//                    $model->getProvider()->buildCommand('pull', ['origin', $remoteBranch]),
//                    $this->isDryRun(),
//                    false
//                );
//            }
        }

        $parallelProcess->run();
    }
}
