<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\CommentHelper;
use Octava\GeggsBundle\Helper\RepositoryList;

/**
 * Class CommitVendorPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class CommitVendorPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     * @return string
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);
        $comment = $this->getInput()->getOption('message');
        $branch = $repositories->getProjectModel()->getBranch();
        $comment = CommentHelper::buildComment($comment, $branch);

        foreach ($repositories->getVendorModels() as $model) {
            if ($model->hasChanges()) {
                $model->getProvider()->run('add', ['.'], $this->isDryRun());

                $params = [];
                if ($this->getInput()->hasOption('no-verify') && $this->getInput()->getOption('no-verify')) {
                    $params[] = '--no-verify';
                }
                $params[] = '-m';
                $params[] = $comment;
                $model->getProvider()->run('commit', $params, $this->isDryRun(), true);

                $this->getSymfonyStyle()->writeln(
                    sprintf('%s: commit changes to (%s)', $model->getPath(), $model->getBranch())
                );
            } else {
                $this->getLogger()->debug('Changes not found', ['commit vendor']);
            }
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
