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
        /** @var RepositoryModel[] $list */
        if (!$this->getInput()->getOption('no-vendors')) {
            $list = array_reverse($repositories->getAll());
        } else {
            $list = [$repositories->getProjectModel()];
        }

        $progressBar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressBar->create(count($list));
        foreach ($list as $model) {
            $currentBranch = $model->getBranch();
            $progressBar->advance('Checkout of '.($model->getPath() ?: 'project repository'));
            if ($currentBranch == $branch) {
                continue;
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
                $output = $model->getProvider()->run('fetch', [], $this->isDryRun());
                if ($output) {
                    $this->getSymfonyStyle()->writeln($output);
                }

                $arguments = [];
                if (!$model->getProvider()->hasLocalBranch($branch)
                    && !$model->getProvider()->hasRemoteBranch($branch)
                ) {
                    $arguments[] = '-b';
                }

                $arguments[] = $branch;
                $model->getProvider()->run('checkout', $arguments, $this->isDryRun());

                $this->getSymfonyStyle()->newLine();
                $this->getSymfonyStyle()->writeln(sprintf('%s: switched to [%s]', $model->getPath(), $branch));
            }
        }

        $progressBar->finish();

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
