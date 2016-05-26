<?php
namespace Octava\GeggsBundle\Plugin;

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
        $list = array_reverse($repositories->getAll());
        foreach ($list as $model) {
            $currentBranch = $model->getBranch();

            if ($currentBranch == $branch) {
                continue;
            }

            $needCheckout = $model->getType() == RepositoryModel::TYPE_ROOT;
            $needCheckout = $needCheckout || $model->hasChanges();
            $needCheckout = $needCheckout || 'master' == $branch;
            $needCheckout = $needCheckout || $model->getProvider()->hasLocalBranch($branch);
            $needCheckout = $needCheckout || $model->getProvider()->hasRemoteBranch($branch);

            if ($model->hasCommits()) {
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

                $this->getSymfonyStyle()->writeln(sprintf('%s: switched to [%s]', $model->getPath(), $branch));
            }
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
