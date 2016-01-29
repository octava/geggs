<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class PushPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class PullPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $remoteBranch = $this->getInput()->getArgument('remote-branch');
        /** @var RepositoryModel[] $list */
        $list = array_reverse($repositories->getAll());
        foreach ($list as $model) {
            if ($model->hasCommits()) {
                $currentBranch = $model->getBranch();

                $this->io->writeln(sprintf('%s pulled from %s', $model->getPath(), $currentBranch));

                $output = $model->getProvider()->run('pull', ['origin', $currentBranch], $this->isDryRun(), true);
                $this->io->writeln($output);

                $output = $model->getProvider()->run('pull', ['origin', $remoteBranch], $this->isDryRun(), true);
                $this->io->writeln($output);
            } else {
                $this->getLogger()->debug('Nothing to push', ['name' => $model->getPath()]);
            }
        }
    }
}
