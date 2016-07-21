<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ProgressBarHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class CheckoutPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class CheckoutPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $branch = $this->getInput()->getArgument('branch');
        $noVendors = $this->getInput()->getOption('no-vendors');
        $customVendors = $this->getInput()->getOption('repo');
        $allRepositories = $this->getRepositoriesWithKeys($repositories);

        /** @var RepositoryModel[] $list */
        $list = [];
        if ($noVendors) {
            $list = [null];
        } elseif (!empty($customVendors)) {
            foreach ($customVendors as $vendor) {
                foreach ($allRepositories as $repositoryModel) {
                    $originalPackageName = strtolower($repositoryModel->getPackageName());
                    $vendor = rtrim($vendor, DIRECTORY_SEPARATOR);
                    $packageName = strtolower(substr($vendor, strlen($originalPackageName) * -1));
                    if ($originalPackageName == $packageName) {
                        $list[] = $repositoryModel->getPackageName();
                        break;
                    }
                }
            }
        } else {
            $list = array_keys($allRepositories);
        }
        $list = array_unique($list);

        $progressBar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressBar->create(count($allRepositories));
        /** @var RepositoryModel $model */
        foreach ($allRepositories as $packageName => $model) {
            $currentBranch = $model->getBranch();
            $progressBar->advance('Checkout '.($model->getPath() ?: 'project repository'));
            if ($currentBranch == $branch) {
                continue;
            }

            if ($model->hasCommits()) {
                $this->getSymfonyStyle()->newLine();
                $this->getSymfonyStyle()
                    ->warning(
                        sprintf(
                            'Вы делаете checkout "%s" с закоммиченными но не запушенными правками',
                            $model->getPackageName()
                        )
                    );
            }

            if ($model->getType() == RepositoryModel::TYPE_ROOT) {
                $needCheckout = true;
            } else {
                //
            }

            $needCheckout = $model->getType() == RepositoryModel::TYPE_ROOT;
            $needCheckout = $needCheckout || $model->hasChanges();
            $needCheckout = $needCheckout || 'master' == $branch;
            $needCheckout = $needCheckout || $model->getProvider()->hasLocalBranch($branch);
            $needCheckout = $needCheckout || $model->getProvider()->hasRemoteBranch($branch);

            if ($model->hasCommits()) {
                $this->getSymfonyStyle()->newLine();
                $this->getSymfonyStyle()
                    ->warning(
                        sprintf(
                            'Вы делаете checkout "%s" с закоммиченными но не запушенными правками',
                            $model->getPackageName()
                        )
                    );
            }

            if ($needCheckout) {
                $output = $model->getProvider()->run('fetch', [], $this->isDryRun(), true);
                if ($output) {
                    $this->getSymfonyStyle()->writeln($output);
                }

                $arguments = [];
                if (!$model->getProvider()->hasLocalBranch($branch)
                    && !$model->getProvider()->hasRemoteBranch($branch)
                ) {
                    $arguments[] = '-b';
                }

                $this->getSymfonyStyle()->newLine(2);
                $arguments[] = $branch;
                $model->getProvider()->run('checkout', $arguments, $this->isDryRun(), true);
                $this->getSymfonyStyle()->writeln(sprintf('%s: switched to [%s]', $model->getPath(), $branch));
            }
        }

        $progressBar->finish();

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }

    /**
     * @param RepositoryList $repositoryList
     * @return RepositoryModel[]
     */
    protected function getRepositoriesWithKeys(RepositoryList $repositoryList)
    {
        $result = [];
        foreach ($repositoryList->getAll() as $repository) {
            $result[$repository->getPackageName()] = $repository;
        }

        return $result;
    }
}
