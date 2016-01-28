<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;

/**
 * Class CommitProjectPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class CommitProjectPlugin extends AbstractPlugin
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

        $model = $repositories->getProjectModel();
        if ($model->hasChanges()) {
            $model->getProvider()->run('commit', ['-am', $comment], $this->isDryRun());
        }
    }
}
