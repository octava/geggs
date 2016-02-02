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
     *  @param RepositoryList $repositories
     * @description
     * проверить измененные файлы в директории
     * если есть измененные - проверяем название ветки
     * берем название проектной ветки
     * собираем вендоры для которых нужно создать ветки
     * если ветки есть - запрашиваем подтверждение
     */
    public function execute(RepositoryList $repositories)
    {
        $branch = $repositories->getProjectModel()->getBranch();
        $vendorsWithoutBranch = $this->findVendorsWithoutBranch($repositories->getVendorModels(), $branch);
        $this->createBranches($branch, $vendorsWithoutBranch);
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
                $this->stopPropagation();
            } else {
                foreach ($vendorsWithoutBranch as $model) {
                    $model->getProvider()->run('checkout', ['-b', $rootBranch]);
                }
            }
        }
    }
}
