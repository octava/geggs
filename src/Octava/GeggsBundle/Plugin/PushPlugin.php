<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PushPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PushPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        /** @var RepositoryModel[] $list */
        $list = array_reverse($repositories->getAll());
        foreach ($list as $model) {
            if ($model->hasCommits() || !$model->hasRemote()) {
                $branch = $model->getBranch();

                $model->getProvider()->run('push', ['origin', $branch], $this->isDryRun());
                $this->getSymfonyStyle()->writeln(sprintf('%s pushed to %s', $model->getPath(), $branch));
            } else {
                $this->getLogger()->debug('Nothing to push', ['name' => $model->getPath()]);
            }
        }
    }
}
