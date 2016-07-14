<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\CommentHelper;
use Octava\GeggsBundle\Helper\ProgressBarHelper;
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

        $vendors = $repositories->getVendorModels();
        $this->getSymfonyStyle()->newLine();
        $progressBar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressBar->create(count($vendors));

        foreach ($vendors as $model) {
            $progressBar->advance($model->getPath());
            if ($model->hasChanges()) {
                if ($model->hasConflicts()) {
                    $progressBar->finish();

                    $this->getSymfonyStyle()->newLine();
                    $this->getSymfonyStyle()->error('Conflicts detected');
                    $this->getSymfonyStyle()->writeln($model->getPath());
                    $this->getSymfonyStyle()->writeln($model->getConflicts());
                    $this->getSymfonyStyle()->newLine();
                    $this->stopPropagation();
                    break;
                }

                $model->getProvider()->run('add', ['.'], $this->isDryRun());

                $params = [];
                if ($this->getInput()->hasOption('no-verify') && $this->getInput()->getOption('no-verify')) {
                    $params[] = '--no-verify';
                }
                $params[] = '-m';
                $params[] = $comment;
                $this->getSymfonyStyle()->newLine();
                $model->getProvider()->run('commit', $params, $this->isDryRun(), true);

                $this->getSymfonyStyle()->writeln(
                    sprintf('%s: commit changes to (%s)', $model->getPath(), $model->getBranch())
                );
            } else {
                $this->getLogger()->debug('Changes not found', ['commit vendor']);
            }
        }
        $progressBar->finish();

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
