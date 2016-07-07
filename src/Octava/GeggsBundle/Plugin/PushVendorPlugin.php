<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ParallelProcess;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PushPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PushVendorPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);
        $parallelProcess = new ParallelProcess($this->getSymfonyStyle());

        /** @var RepositoryModel[] $list */
        $list = $repositories->getVendorModels();
        foreach ($list as $model) {
            if ($model->hasCommits() || !$model->hasRemote()) {
                $branch = $model->getBranch();

                $parallelProcess->add(
                    $model->getProvider()->buildCommand('pull', ['origin', $branch]),
                    $this->isDryRun(),
                    false
                );
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

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
