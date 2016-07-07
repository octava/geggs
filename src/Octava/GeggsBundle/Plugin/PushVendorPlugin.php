<?php
namespace Octava\GeggsBundle\Plugin;

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

        /** @var RepositoryModel[] $list */
        $list = $repositories->getVendorModels();
        foreach ($list as $model) {
            if ($model->hasCommits() || !$model->hasRemote()) {
                $branch = $model->getBranch();

                $model->getProvider()->run('pull', ['origin', $branch], $this->isDryRun(), true);
                $model->getProvider()->run('push', ['origin', $branch], $this->isDryRun(), true);

                $this->getSymfonyStyle()->writeln(sprintf('%s pushed to %s', $model->getPath(), $branch));
            } else {
                $this->getLogger()->debug('Nothing to push', ['name' => $model->getPath()]);
            }
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
