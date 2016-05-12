<?php
namespace Octava\GeggsBundle\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use GrumPHP\Console\Command\Git\DeInitCommand;
use GrumPHP\Console\Command\Git\InitCommand;
use Octava\GeggsBundle\Config;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Yaml;

class GrumPHPPlugin implements PluginInterface, EventSubscriberInterface
{
    const PACKAGE_NAME = 'octava/geggs';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var bool
     */
    protected $configureScheduled = false;

    /**
     * @var bool
     */
    protected $initScheduled = false;

    /**
     * Attach package installation events:
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => ['postPackageInstall', 100],
            PackageEvents::POST_PACKAGE_UPDATE => ['postPackageUpdate', 100],
            PackageEvents::PRE_PACKAGE_UNINSTALL => ['prePackageUninstall', 100],
            ScriptEvents::POST_INSTALL_CMD => ['runScheduledTasks', 100],
            ScriptEvents::POST_UPDATE_CMD => ['runScheduledTasks', 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * When this package is updated, the git hook is also initialized
     *
     * @param PackageEvent $event
     */
    public function postPackageInstall(PackageEvent $event)
    {
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();

        if (!$this->guardIsGrumPhpPackage($package)) {
            return;
        }

        // Schedule init when command is completed
        $this->configureScheduled = true;
        $this->initScheduled = true;
    }

    /**
     * When this package is updated, the git hook is also updated
     *
     * @param PackageEvent $event
     */
    public function postPackageUpdate(PackageEvent $event)
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getTargetPackage();

        if (!$this->guardIsGrumPhpPackage($package)) {
            return;
        }

        // Schedule init when command is completed
        $this->initScheduled = true;
    }

    /**
     * When this package is uninstalled, the generated git hooks need to be removed
     *
     * @param PackageEvent $event
     */
    public function prePackageUninstall(PackageEvent $event)
    {
        /** @var UninstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();

        if (!$this->guardIsGrumPhpPackage($package)) {
            return;
        }

        // First remove the hook, before everything is deleted!
        $this->deInitGitHook();
    }

    /**
     * @param Event $event
     */
    public function runScheduledTasks(Event $event)
    {
        $this->initScheduled = true;
        if ($this->initScheduled) {
            $this->initGitHook();
        }
    }

    /**
     * @param PackageInterface $package
     *
     * @return bool
     */
    protected function guardIsGrumPhpPackage(PackageInterface $package)
    {
        return $package->getName() == self::PACKAGE_NAME;
    }

    /**
     * Initialize git hooks
     */
    protected function initGitHook()
    {
        $this->runGrumPhpCommand(InitCommand::COMMAND_NAME);
    }

    /**
     * Deinitialize git hooks
     */
    protected function deInitGitHook()
    {
        $this->runGrumPhpCommand(DeInitCommand::COMMAND_NAME);
    }

    /**
     * Run the GrumPHP console to (de)init the git hooks
     *
     * @param $command
     */
    protected function runGrumPhpCommand($command)
    {
        $config = $this->composer->getConfig();
        $baseDir = $this->getObjectProtectedProperty($config, 'baseDir');

        $configFilename = !empty($config->get('extra')['grumphp']['config-default-path'])
            ? $config->get('extra')['grumphp']['config-default-path'] : $baseDir.DIRECTORY_SEPARATOR.'grumphp.yml';

        if (!file_exists($configFilename)) {
            // Check executable which is running:
            if ($this->io->isVeryVerbose()) {
                $this->io->write(sprintf('<fg=red>File "%s" not found</fg=red>', $configFilename));
            }

            return;
        }

        $application = new \Octava\GeggsApplication();
        /** @var Config $config */
        $config = $application->getContainer()->get('octava_geggs.config');

        $grumPhpConfigData = Yaml::parse(file_get_contents($configFilename));
        $fileSystem = new Filesystem();

        foreach ($config->getVendorDirs() as $item) {
            $grumPhpConfigData['bin_dir'] = '../../../bin';

            $grumPhpConfigYml = Yaml::dump($grumPhpConfigData);

            $vendorConfigFilename = implode(DIRECTORY_SEPARATOR, [$item, 'vendor', 'grumphp.yml']);
            $fileSystem->dumpFile($vendorConfigFilename, $grumPhpConfigYml);

            $gitPreCommitFilename = implode(DIRECTORY_SEPARATOR, [$item, '.git', 'hooks', 'pre-commit']);
            $fileSystem->dumpFile(
                $gitPreCommitFilename,
                $this->generatePreCommit($vendorConfigFilename)
            );

            $gitCommitMsgFilename = implode(DIRECTORY_SEPARATOR, [$item, '.git', 'hooks', 'commit-msg']);
            $fileSystem->dumpFile(
                $gitCommitMsgFilename,
                $this->generateCommitMsg($vendorConfigFilename)
            );

            if ($this->io->isVeryVerbose()) {
                $this->io->write(sprintf('Config created %s', $vendorConfigFilename));
                $this->io->write(sprintf('Pre commit hook created %s', $gitPreCommitFilename));
                $this->io->write(sprintf('Commit msg hook created %s', $gitCommitMsgFilename));
            }
        }

        $this->io->write('<fg=yellow>GrumPHP is sniffing your vendors code!</fg=yellow>');
    }

    protected function getObjectProtectedProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        $property->setAccessible(false);

        return $result;
    }

    protected function generatePreCommit($path)
    {
        $result = <<<'SQL'
#!/bin/sh

#
# Run the hook command.
# Note: this will be replaced by the real command during copy.
#
(cd "./" && exec '../../../bin/grumphp' 'git:pre-commit' '--config={{path}}' '--skip-success-output')

# Validate exit code of above command
RC=$?
if [ "$RC" != 0 ]; then
    exit $RC;
fi

# Clean exit:
exit 0;
SQL;
        $result = str_replace('{{path}}', $path, $result);

        return $result;
    }

    private function generateCommitMsg($path)
    {
        $result = <<<'SQL'
#!/bin/sh

#
# Run the hook command.
# Note: this will be replaced by the real command during copy.
#

GIT_USER=$(git config user.name)
GIT_EMAIL=$(git config user.email)
COMMIT_MSG_FILE=$1

(cd "./" && exec '../../../bin/grumphp' 'git:commit-msg' '--config={{path}}' "--git-user=$GIT_USER" "--git-email=$GIT_EMAIL" "$COMMIT_MSG_FILE")

# Validate exit code of above command
RC=$?
if [ "$RC" != 0 ]; then
    exit $RC;
fi

# Clean exit:
exit 0;
SQL;
        $result = str_replace('{{path}}', $path, $result);

        return $result;
    }
}
