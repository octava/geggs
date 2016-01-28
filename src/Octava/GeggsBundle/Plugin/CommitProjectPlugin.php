<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

/**
 * Class CommitProjectPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class CommitProjectPlugin extends AbstractPlugin
{
    protected $comment;

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @param RepositoryList $repositories
     * @return string
     */
    public function execute(RepositoryList $repositories)
    {
        $comment = $this->getComment();
        if (empty($comment)) {
            $comment = trim($this->io->ask('Enter comment, please', null, new NotBlankValidator()));
            $branch = $repositories->getProjectModel()->getBranch();
            if (false === strpos($comment, $branch)) {
                $comment = $branch.': '.$comment;
            }
        }

        foreach ($repositories->getVendorModels() as $model) {
            if ($model->hasChanges()) {
                $model->getProvider()->run('commit', ['-am', $comment]);
            }
        }
    }
}
