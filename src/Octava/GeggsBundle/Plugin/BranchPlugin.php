<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Model\RepositoryModel;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BranchPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class BranchPlugin extends AbstractPlugin
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * ComposerPlugin constructor.
     * @param Config $config
     * @param SymfonyStyle $io
     */
    public function __construct(Config $config, SymfonyStyle $io)
    {
        $this->config = $config;
        $this->io = $io;
    }

    /**
     * @param RepositoryModel[] $repositories
     * @description
     * проверить измененные файлы в директории
     * если есть измененные - проверяем название ветки
     * берем название проектной ветки
     * собираем вендоры для которых нужно создать ветки
     * если ветки есть - запрашиваем подстверждение
     */
    public function execute(array $repositories)
    {
        /** @var RepositoryModel $rootRepository */
        /** @var RepositoryModel[] $vendors */
        list($rootRepository, $vendors) = $this->getRepositories($repositories);
        $branch = $rootRepository->getBranch();
        $vendorsWithoutBranch = $this->findVendorsWithoutBranch($vendors, $branch);
        $this->createBranches($vendorsWithoutBranch);
    }

    /**
     * @param RepositoryModel[] $repositories
     * @return array
     */
    protected function getRepositories(array $repositories)
    {
        /** @var RepositoryModel $rootRepository */
        $rootRepository = null;
        /** @var RepositoryModel[] $vendors */
        $vendors = [];
        foreach ($repositories as $repository) {
            if ($repository->getType() === RepositoryModel::TYPE_VENDOR) {
                $vendors[] = $repository;
            } else {
                $rootRepository = $repository;
            }
        }

        return [$rootRepository, $vendors];
    }

    /**
     * @param RepositoryModel[] $vendors
     * @param string $branch
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
     * @param $vendorsWithoutBranch
     */
    protected function createBranches($vendorsWithoutBranch)
    {
        if ($vendorsWithoutBranch) {
            $this->io->caution('There are vendors without branches:');
            $this->io->listing($vendorsWithoutBranch);
            if (!$this->io->confirm('Create branches?')) {
                $this->stopPropagation();
            } else {
                // --- dump ---
                echo '<pre>';
                echo __FILE__.chr(10);
                echo __METHOD__.chr(10);
                var_dump(123);
                echo '</pre>';
                exit(0);
                // --- // ---
            }
        }
    }
}
