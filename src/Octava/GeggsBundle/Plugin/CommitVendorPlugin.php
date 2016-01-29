<?php
namespace Octava\GeggsBundle\Plugin;

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
        $comment = $this->getInput()->getOption('message');
        if (empty($comment)) {
            $comment = trim(
                $this->io->ask(
                    'Enter comment, please',
                    null,
                    function ($answer) {
                        $answer = trim($answer);
                        if (empty($answer)) {
                            throw new \RuntimeException('Empty comment');
                        }

                        return $answer;
                    }
                )
            );
        }
        $branch = $repositories->getProjectModel()->getBranch();
        if (false === strpos($comment, $branch)) {
            $comment = $branch.': '.$comment;
        }
        $this->getInput()->setOption('message', $comment);

        foreach ($repositories->getVendorModels() as $model) {
            if ($model->hasChanges()) {
                $model->getProvider()->run('add', ['.'], $this->isDryRun());
                $model->getProvider()->run('commit', ['-m', $comment], $this->isDryRun());

                $this->io->writeln(sprintf('%s: commit changes', $model->getPath()));
            } else {
                $this->getLogger()->debug('Changes not found');
            }
        }
    }
}
