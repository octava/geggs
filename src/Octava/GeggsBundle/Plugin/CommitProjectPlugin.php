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
        //добавить hasChanges()
        $comment = $this->getInput()->getOption('message');
        if (empty($comment)) {
            $comment = trim(
                $this->getSymfonyStyle()->ask(
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
            $model->getProvider()->run('add', ['.'], $this->isDryRun());
            $model->getProvider()->run('commit', ['-m', $comment], $this->isDryRun());
        } else {
            $this->getLogger()->debug('Changes not found');
        }
    }
}
