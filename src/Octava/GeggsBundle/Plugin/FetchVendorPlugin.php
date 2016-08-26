<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ParallelProcess;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class FetchVendorPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class FetchVendorPlugin extends AbstractPlugin
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

        $model = null;
        foreach ($list as $model) {
            $parallelProcess->add(
                $model->getProvider()->buildCommand('fetch', []),
                $model->getAbsolutePath(),
                $this->isDryRun(),
                false
            );
        }

        $parallelProcess->run();

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
