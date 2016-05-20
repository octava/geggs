<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

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
        $this->getLogger()->debug('Run plugin', [get_called_class()]);
        $composerFilename = $repositories->getProjectModel()->getAbsolutePath().DIRECTORY_SEPARATOR.'composer.json';
        $composerData = json_decode(file_get_contents($composerFilename), true);

        if (!$composerData) {
            throw new \RuntimeException('Json decode error: '.json_last_error_msg());
        }

        $data = $repositories->getVendorModels();
        $vendorsModels = [];
        foreach ($data as $model) {
            $vendorsModels[strtolower($model->getPackageName())] = $model;
        }

        $projectBranch = $repositories->getProjectModel()->getBranch();
        $updateFlag = false;
        if (!empty($composerData['require'])) {
            $changes = $this->changeVersion(
                $composerData['require'],
                $vendorsModels,
                $projectBranch
            );
            $composerData['require'] = array_merge($composerData['require'], $changes);
            $updateFlag = $updateFlag || !empty($changes);
        }

        if (!empty($composerData['require-dev'])) {
            $changes = $this->changeVersion(
                $composerData['require-dev'],
                $vendorsModels,
                $projectBranch
            );
            $composerData['require-dev'] = array_merge($composerData['require-dev'], $changes);
            $updateFlag = $updateFlag || !empty($changes);
        }

        if (!$this->isDryRun()) {
            $jsonEncodedData = json_encode(
                $composerData,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            file_put_contents($composerFilename, $jsonEncodedData);
        }

        if ($updateFlag) {
            $this->getSymfonyStyle()->success('File composer.json updated');
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }

    /**
     * @param array             $composerData
     * @param RepositoryModel[] $vendorsModels
     * @param string            $projectBranch
     * @return array
     */
    protected function changeVersion(array $composerData, array $vendorsModels, $projectBranch)
    {
        $result = [];
        foreach ($composerData as $packageName => $sourceVersion) {
            $packageNameLower = strtolower($packageName);

            $this->getLogger()->debug('Check vendor version', [$packageName, $sourceVersion]);

            if (!array_key_exists($packageNameLower, $vendorsModels)) {
                $this->getLogger()->debug('Skipped, because not found in vendor list',
                    ['packageNameLower' => $packageNameLower]);
                continue;
            }
            $model = $vendorsModels[$packageNameLower];

            $versionChanged = (false !== strpos($sourceVersion, ' as '));
            $newVersion = 'dev-'.$model->getBranch().' as '.$sourceVersion;

            if (!$versionChanged &&
                ($model->hasChanges()
                    || 'master' != $model->getBranch())
            ) {
                $result[$packageName] = $newVersion;

                $this->getLogger()->debug(
                    'Change version 1',
                    [
                        'vendor' => $packageName,
                        'from' => $sourceVersion,
                        'to' => $newVersion,
                    ]
                );
            } elseif (
                'master' == $projectBranch
                && $versionChanged
            ) {
                $ar = explode('as', $sourceVersion);
                $newVersion = trim($ar[1]);
                $result[$packageName] = $newVersion;

                $this->getLogger()->debug(
                    'Change version 2',
                    [
                        'vendor' => $packageName,
                        'from' => $sourceVersion,
                        'to' => $newVersion,
                    ]
                );
            } else {
                $this->getLogger()->debug('No changes', ['vendor' => $packageName]);
            }
        }

        return $result;
    }
}
