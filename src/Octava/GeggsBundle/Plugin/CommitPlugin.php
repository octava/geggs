<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Model\RepositoryModel;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

/**
 * Class CommitPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class CommitPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryModel[] $repositories
     */
    public function execute(array $repositories)
    {
        /** @var RepositoryModel $projectRepository */
        /** @var RepositoryModel[] $vendors */
        list($projectRepository, $vendors) = $this->getRepositories($repositories);

        $comment = trim($this->io->ask('Enter comment, please', null, new NotBlankValidator()));
        if (false === strpos($comment, $projectRepository->getBranch())) {
            $comment = $projectRepository->getBranch().': '.$comment;
        }

        foreach ($vendors as $vendor) {
            if ($vendor->hasChanges()) {
                $vendor->getProvider()->run('commit', ['-am', $comment]);
            }
        }
    }
}
