<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ParallelProcess;
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
        $parallelProcess = new ParallelProcess($this->getSymfonyStyle());

        /** @var RepositoryModel[] $list */
        $list = array_reverse($repositories->getAll());
        foreach ($list as $model) {
            if ($model->hasCommits() || !$model->hasRemote()) {
                $branch = $model->getBranch();

                $parallelProcess->add(
                    $model->getProvider()->buildCommand('push', ['origin', $branch]),
                    $this->isDryRun(),
                    false
                );

                $this->getSymfonyStyle()->writeln(sprintf('%s pushed to %s', $model->getPath(), $branch));
            } else {
                $this->getLogger()->debug('Nothing to push', ['name' => $model->getPath()]);
            }
        }

        $parallelProcess->run();
    }
}
