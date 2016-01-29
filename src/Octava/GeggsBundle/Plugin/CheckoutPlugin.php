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

            if ($model->hasCommits()) {
                $this->io->warning('Вы делаете checkout с закоммиченными но не запушенными правками');
            }

            if ($needCheckout) {
                $this->io->writeln(sprintf('%s pulled from %s', $model->getPath(), $currentBranch));

                $output = $model->getProvider()->run('pull', ['origin', $currentBranch], $this->isDryRun(), true);
                if ($output) {
                    $this->io->writeln($output);
                }

                $output = $model->getProvider()->run('fetch', [], $this->isDryRun());
                if ($output) {
                    $this->io->writeln($output);
                }

                $arguments = [];
                if (!$model->getProvider()->hasLocalBranch($branch)
                    && !$model->getProvider()->hasRemoteBranch($branch)
                ) {
                    $arguments[] = '-b';
                }
                $arguments[] = $branch;
                $model->getProvider()->run('checkout', $arguments, $this->isDryRun());

                $this->io->writeln(sprintf('%s: switched to branch', $model->getPath(), $branch));
            }
        }
    }
}
