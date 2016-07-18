<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ComposerHelper;
use Octava\GeggsBundle\Helper\ProgressBarHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class ComposerPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class ComposerJsonPlugin extends AbstractPlugin
{

    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $model = $repositories->getProjectModel();
        if ($model->hasConflicts()) {
            if (false !== stripos($model->getConflicts(), ComposerHelper::COMPOSER_JSON)) {
                $this->getSymfonyStyle()->error('You should resolve composer.json conflict first');
                $this->stopPropagation();

                return;
            }
        }

        $composerFilename = $repositories->getProjectModel()->getAbsolutePath() . DIRECTORY_SEPARATOR . 'composer.json';

        $helper = new ComposerHelper();
        $composerData = $helper->jsonDecode(file_get_contents($composerFilename));

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
     * @param array $composerData
     * @param RepositoryModel[] $vendorsModels
     * @param string $projectBranch
     * @return array
     */
    protected function changeVersion(array $composerData, array $vendorsModels, $projectBranch)
    {
        $result = [];

        $progressbar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressbar->create(count($composerData));

        foreach ($composerData as $packageName => $sourceVersion) {
            $packageNameLower = strtolower($packageName);

            $this->getLogger()->debug('Check vendor version', [$packageName, $sourceVersion]);

            if (!array_key_exists($packageNameLower, $vendorsModels)) {
                $this->getLogger()->debug(
                    'Skipped, because not found in vendor list',
                    ['packageNameLower' => $packageNameLower]
                );
                continue;
            }

            $model = $vendorsModels[$packageNameLower];
            $branch = $model->getBranch();
            $progressbar->advance($model->getPackageName());

            $parts = explode('as', $sourceVersion);
            $parts = array_map('trim', $parts);
            if (1 == count($parts)) {
                $currentBranch = 'master';
                $baseVersion = $parts[0];
            } elseif (2 == count($parts)) {
                $currentBranch = $parts[0];
                $baseVersion = $parts[1];
            } else {
                throw new \RuntimeException(sprintf('Invalid %s version %s in composer.json', $packageName, $sourceVersion));
            }

            if ($currentBranch != $branch) {
                if ('master' == $branch) {
                    $newVersion = $baseVersion;
                } else {
                    $newVersion = 'dev-' . $branch . ' as ' . $baseVersion;
                }

                if ($sourceVersion !== $newVersion) {
                    $result[$packageName] = $newVersion;

                    $this->getLogger()->debug(
                        'Change version',
                        [
                            'vendor' => $packageName,
                            'from' => $sourceVersion,
                            'to' => $newVersion,
                        ]
                    );
                }
            }
        }
        $progressbar->finish();

        return $result;
    }
}
