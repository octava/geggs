<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ParallelProcess;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class FetchPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class FetchPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);
        $parallelProcess = new ParallelProcess($this->getSymfonyStyle());

        /** @var RepositoryModel[] $list */
        $list = array_reverse($repositories->getAll());

        $model = null;
        foreach ($list as $model) {
            $parallelProcess->add(
                $model->getProvider()->buildCommand('fetch', []),
                $this->isDryRun(),
                false
            );
        }

        $parallelProcess->run();

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
