<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;

/**
 * Class ComposerPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class ComposerPlugin extends AbstractPlugin
{

    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $composerFilename = $repositories->getProjectModel()->getAbsolutePath().DIRECTORY_SEPARATOR.'composer.json';
        $data = json_decode(file_get_contents($composerFilename), true);

        // --- dump ---
        echo '<pre>';
        echo __FILE__.chr(10);
        echo __METHOD__.chr(10);
        var_dump($data);
        echo '</pre>';
        // --- // ---

        $composerData = $this->loadComposerJsonData();

        foreach ($repositories->getVendorModels() as $model) {
//            $this->modifyRequire($dir, $composerData);
//            $this->modifyrepositories($dir, $composerData);
        }

        $this->modifyComposerJson($composerData);
    }

    protected function loadComposerJsonData()
    {
        return [];
    }

    protected function modifyComposerJson($composerData)
    {
    }

    protected function modifyrepositories($dir, $composerData)
    {
    }

    protected function modifyRequire($dir, $composerData)
    {
    }
}
