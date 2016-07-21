<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ComposerHelper;
use Octava\GeggsBundle\Helper\ProgressBarHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class ComposerUpdatePlugin
 * @package Octava\GeggsBundle\Plugin
 */
class ComposerUpdatePlugin extends AbstractPlugin
{

    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $composerFilename = $repositories->getProjectModel()->getAbsolutePath() . DIRECTORY_SEPARATOR . 'composer.json';
        $helper = new ComposerHelper();
        $composerData = $helper->jsonDecode(file_get_contents($composerFilename));

        $requireData = [];
        $requireDevData = [];
        $counter = count($requireData) + count($requireDevData);
        $vendorsModels = $repositories->getVendorModels();

        $progressbar = new ProgressBarHelper($this->getSymfonyStyle());
        $progressbar->create($counter);

        $needToUpdate = [];
        foreach ($composerData['require'] as $packageName => $sourceVersion) {
            $packageNameLower = strtolower($packageName);
            $progressbar->advance($packageName);

            if (!array_key_exists($packageNameLower, $vendorsModels)) {
                $this->getLogger()->debug(
                    'Skipped, because not found in vendor list',
                    ['packageNameLower' => $packageNameLower]
                );
                continue;
            }

            if ('1.0.x-dev' != $sourceVersion) {
                $needToUpdate[] = $packageNameLower;
            }
        }
        $progressbar->finish();

        if (empty($needToUpdate)) {
            $this->getLogger()->debug('Nothing to update');
        } else {
            $this->getLogger()->info('Updating vendors', $needToUpdate);
            $provider = $repositories->getProjectModel()->getProvider();
            $cmd = 'sleep 15 && ' . $this->getConfig()->getComposerBin().' update '.implode(' ', $needToUpdate);
            $this->getSymfonyStyle()->writeln($cmd);
            $provider->runCommand($cmd, $this->isDryRun(), true);
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
