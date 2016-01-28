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
        $composerData = json_decode(file_get_contents($composerFilename), true);

        foreach ($repositories->getVendorModels() as $model) {
            if ($model->hasChanges()) {
                $packageName = $model->getPackageName();
                $version = 'dev-'.$model->getBranch();

                $composerData['require'][$packageName] = $version;

                $this->getLogger()->debug('Change vendor version', ['vendor' => $packageName, 'version' => $version]);
            }
        }

        if (!$this->isDryRun()) {
            file_put_contents($composerFilename, json_encode($composerData, JSON_PRETTY_PRINT));
        }
        $this->io->success('File composer.json updated');
    }
}
