<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\ComposerHelper;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;
use Symfony\Component\Filesystem\Filesystem;

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
        $model = $repositories->getProjectModel();
        $currentBranch = $model->getBranch();

        if ($model->hasConflicts()) {
            if (false !== stripos($model->getConflicts(), ComposerHelper::COMPOSER_JSON)) {
                $this->getSymfonyStyle()->error('You should resolve composer.json conflict first');
                $this->stopPropagation();

                return;
            }
            if (false !== stripos($model->getConflicts(), ComposerHelper::COMPOSER_LOCK)) {
                try {
                    $this->getLogger()->debug('Auto-resolve composer.lock conflict');
                    $vendorsForUpdate = $this->findVendorsForUpdate($model);

                    $this->resolveComposerConflict($model, ComposerHelper::COMPOSER_LOCK, $currentBranch);
                    if ($vendorsForUpdate) {
                        $cmd = $this->getConfig()->getComposerBin().' update '.implode(' ', $vendorsForUpdate);
                        $this->getSymfonyStyle()->writeln($cmd);
                        $model->getProvider()->runCommand($cmd, $this->isDryRun(), false);
                    }
                    $model->getProvider()->run('add', [ComposerHelper::COMPOSER_LOCK], $this->isDryRun(), false);
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage(), [$e->getTraceAsString()]);
                    $this->getSymfonyStyle()->error($e->getMessage());
                    $this->stopPropagation();
                }
            }
        }
    }

    /**
     * @param RepositoryModel $model
     * @param                 $filename
     * @param string          $currentBranch
     */
    protected function resolveComposerConflict(RepositoryModel $model, $filename, $currentBranch)
    {
        $direction = '--theirs';
        if ('master' === $currentBranch) {
            $direction = '--ours';
        }
        $arguments = [
            $filename,
            $direction,
        ];

        $model->getProvider()->run('checkout', $arguments, $this->isDryRun(), false);
    }

    /**
     * @param RepositoryModel $model
     * @return array
     */
    protected function findVendorsForUpdate(RepositoryModel $model)
    {
        $fileSystem = new Filesystem();
        $fileSystem->copy(
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK,
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK_CONFLICT,
            true
        );

        $model->getProvider()->run('checkout', ['--theirs', ComposerHelper::COMPOSER_LOCK], $this->isDryRun(), false);
        $theirLock = file_get_contents($model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK);
        $fileSystem->copy(
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK_CONFLICT,
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK,
            true
        );

        $model->getProvider()->run('checkout', ['--ours', ComposerHelper::COMPOSER_LOCK], $this->isDryRun(), false);
        $oursLock = file_get_contents($model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK);
        $fileSystem->rename(
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK_CONFLICT,
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_LOCK,
            true
        );

        $helper = new ComposerHelper();
        $vendorsForUpdate = $helper->diff($theirLock, $oursLock);
        $composerJson = file_get_contents(
            $model->getAbsolutePath().DIRECTORY_SEPARATOR.ComposerHelper::COMPOSER_JSON
        );
        $vendors = $helper->extractAllVendors($composerJson);
        $vendorsForUpdate = array_intersect($vendorsForUpdate, $vendors);

        return $vendorsForUpdate;
    }
}
