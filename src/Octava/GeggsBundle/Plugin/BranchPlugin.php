<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class BranchPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class BranchPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     * @description
     * проверить измененные файлы в директории
     * если есть измененные - проверяем название ветки
     * берем название проектной ветки
     * собираем вендоры для которых нужно создать ветки
     * если ветки есть - запрашиваем подтверждение
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $branch = $repositories->getProjectModel()->getBranch();
        $vendorsWithoutBranch = $this->findVendorsWithoutBranch($repositories->getVendorModels(), $branch);
        $this->createBranches($branch, $vendorsWithoutBranch);

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }

    /**
     * @param RepositoryModel[] $vendors
     * @param string            $branch
     * @return RepositoryModel[]
     */
    protected function findVendorsWithoutBranch(array $vendors, $branch)
    {
        /** @var RepositoryModel[] $vendorsWithoutBranch */
        $vendorsWithoutBranch = [];
        foreach ($vendors as $vendor) {
            if ($branch !== $vendor->getBranch() && $vendor->hasChanges()) {
                $vendorsWithoutBranch[] = $vendor;
            }
        }

        return $vendorsWithoutBranch;
    }

    /**
     * @param string            $rootBranch
     * @param RepositoryModel[] $vendorsWithoutBranch
     */
    protected function createBranches($rootBranch, $vendorsWithoutBranch)
    {
        if ($vendorsWithoutBranch) {
            $this->getSymfonyStyle()->caution('There are vendors with different branches:');
            $this->getSymfonyStyle()->listing($vendorsWithoutBranch);
            if (!$this->getSymfonyStyle()->confirm('Create branches?')) {
                if (!$this->getSymfonyStyle()->confirm('Would you like to continue <info>commit</info> procedure?')) {
                    $this->stopPropagation();
                };
            } else {
                foreach ($vendorsWithoutBranch as $model) {
                    $model->getProvider()->run('checkout', ['-b', $rootBranch]);
                }
            }
        }
    }
}
