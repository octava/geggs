<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;

/**
 * Detect vendors for update and update composer.lock
 *
 * Class ComposerLockPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class ComposerLockPlugin extends AbstractPlugin
{

    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {

    }
}
