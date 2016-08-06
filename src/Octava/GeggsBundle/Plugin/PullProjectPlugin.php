<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PullProjectPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PullProjectPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $remoteBranch = null;
        if ($this->getInput()->hasArgument('remote-branch')) {
            $remoteBranch = $this->getInput()->getArgument('remote-branch');
        }
        /** @var RepositoryModel $list */
        $model = $repositories->getProjectModel();

        $currentBranch = $model->getBranch();
        $model->getProvider()->run('fetch', [], $this->isDryRun(), false);

        if ($model->getProvider()->hasRemoteBranch($currentBranch)) {
            $model->getProvider()->run(
                'pull',
                ['origin', $currentBranch],
                $this->isDryRun(),
                false
            );
        }

        $needMerge = !empty($remoteBranch) && $remoteBranch != $currentBranch;

        if ($needMerge && $model->getProvider()->hasRemoteBranch($remoteBranch)) {
            $model->getProvider()->run('pull', ['origin', $remoteBranch], $this->isDryRun(), false);
        }

        if ($needMerge && $model->getProvider()->hasLocalBranch($remoteBranch)) {
            $model->getProvider()->run('merge', [$remoteBranch], $this->isDryRun(), false);
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
