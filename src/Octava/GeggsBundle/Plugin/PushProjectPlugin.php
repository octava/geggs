<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PushPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PushProjectPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $onlyVendor = $this->getInput()->getOption('only-vendor');

        if (!$onlyVendor) {
            /** @var RepositoryModel $list */
            $model = $repositories->getProjectModel();
            if ($model->hasCommits() || !$model->hasRemote()) {
                $branch = $model->getBranch();

                if ($model->hasRemote()) {
                    $model->getProvider()->run('pull', ['origin', $branch], $this->isDryRun(), true);
                }
                $model->getProvider()->run('push', ['origin', $branch], $this->isDryRun(), true);

                $this->getSymfonyStyle()->writeln(sprintf('Project pushed to %s', $branch));
            } else {
                $this->getLogger()->debug('Nothing to push', ['name' => $model->getPath()]);
            }
        } else {
            $this->getLogger()->debug('Only vendor option enabled');
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
