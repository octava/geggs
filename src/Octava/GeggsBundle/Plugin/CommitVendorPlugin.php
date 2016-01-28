<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

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
        $comment = trim($this->io->ask('Enter comment, please', null, new NotBlankValidator()));
        $branch = $repositories->getProjectModel()->getBranch();
        if (false === strpos($comment, $branch)) {
            $comment = $branch.': '.$comment;
        }

        foreach ($repositories->getVendorModels() as $model) {
            if ($model->hasChanges()) {
                $model->getProvider()->run('commit', ['-am', $comment]);
            }
        }

        return $comment;
    }
}
