<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GrumpPhpCommand
 * @package Octava\GeggsBundle\Command
 */
class GrumpPhpCommand extends AbstractCommand
{
    const ACTION_INIT = 'init';
    const ACTION_DROP = 'drop';

    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('grumphp')
            ->addArgument('action', InputArgument::REQUIRED, 'Command action (init|drop)')
            ->setDescription('Record changes to the repository');
    }

    /**
     * @param InputInterface         $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug(
            'Start',
            [
                'command_name' => $this->getName(),
                'args' => $input->getArguments(),
                'opts' => $input->getOptions(),
            ]
        );

        $action = $input->getArgument('action');

        if (!in_array($action, [self::ACTION_INIT, self::ACTION_DROP])) {
            throw  new \RuntimeException(
                sprintf('Invalid argument action, must be "%s" or "%s"', self::ACTION_INIT, self::ACTION_DROP)
            );
        }
        $optionDryRun = $input->getOption('dry-run');

        $prettyOutput = !$output->isQuiet() && !$output->isDebug();

        if ($prettyOutput) {
            $this->getSymfonyStyle()->title(sprintf('grumphp action "%s"', $input->getArgument('action')));
            $this->getSymfonyStyle()->writeln('');
        }

        $repositories = $this->getRepositoryModelList();
        $composerFilename = $repositories->getProjectModel()->getAbsolutePath().DIRECTORY_SEPARATOR.'composer.json';
        $composerData = json_decode(file_get_contents($composerFilename), true);

        if (!$composerData) {
            throw new \RuntimeException('Json decode error: '.json_last_error_msg());
        }

        $configFilename = $repositories->getProjectModel()->getAbsolutePath().DIRECTORY_SEPARATOR.'grumphp.yml';
        if (!empty($composerData['config']['extra']['grumphp']['config-default-path'])) {
            $configFilename = $composerData['config']['extra']['grumphp']['config-default-path'];
        }

        if (!file_exists($configFilename)) {
            $this->getSymfonyStyle()->error(sprintf('File "%s" not found', $configFilename));

            return;
        }

        if ($prettyOutput) {
            $this->getSymfonyStyle()->write(sprintf('Work with GrumPhp file "%s"', $configFilename));
        }

        $grumpConfigData = Yaml::parse(file_get_contents($configFilename));
        $fileSystem = new Filesystem();

        $vendorModels = $repositories->getVendorModels();
        $vendorModelsCnt = count($vendorModels);

        $progress = null;
        if ($prettyOutput) {
            $progress = new ProgressBar($output, $vendorModelsCnt);
            $progress->setFormat("%filename% \n %current%/%max% [%bar%]\n");
            $progress->setBarCharacter('<comment>#</comment>');
            $progress->setEmptyBarCharacter(' ');
            $progress->setProgressCharacter('');
            $progress->setBarWidth(50);
        }
        foreach ($vendorModels as $model) {
            if ($prettyOutput) {
                $progress->setMessage('Working on '.$model->getPath(), 'filename');
                $progress->advance();
            }

            $vendorPath = $model->getAbsolutePath();
            $gitPreCommitFilename = implode(DIRECTORY_SEPARATOR, [$vendorPath, '.git', 'hooks', 'pre-commit']);
            $gitCommitMsgFilename = implode(DIRECTORY_SEPARATOR, [$vendorPath, '.git', 'hooks', 'commit-msg']);
            $vendorConfigFilename = implode(DIRECTORY_SEPARATOR, [$vendorPath, '.git', 'grumphp.yml']);

            if (self::ACTION_INIT == $action) {
                $grumpConfigData['parameters']['bin_dir'] = '../../../bin';

                if (!empty($grumpConfigData['parameters']['tasks']['phpcs']['standard'])) {
                    $standard = $grumpConfigData['parameters']['tasks']['phpcs']['standard'];
                    if (0 === strpos($standard, 'vendor/')
                        || 0 === strpos($standard, './vendor/')
                    ) {
                        $grumpConfigData['parameters']['tasks']['phpcs']['standard'] = implode(
                            DIRECTORY_SEPARATOR,
                            [
                                $repositories->getProjectModel()->getAbsolutePath(),
                                $grumpConfigData['parameters']['tasks']['phpcs']['standard'],
                            ]
                        );
                    }
                }

                if (!$optionDryRun) {
                    $grumpConfigYml = Yaml::dump($grumpConfigData);
                    $fileSystem->dumpFile($vendorConfigFilename, $grumpConfigYml);

                    $fileSystem->dumpFile(
                        $gitPreCommitFilename,
                        $this->generatePreCommit($vendorConfigFilename)
                    );
                    $fileSystem->chmod($gitPreCommitFilename, 0755);

                    $fileSystem->dumpFile(
                        $gitCommitMsgFilename,
                        $this->generateCommitMsg($vendorConfigFilename)
                    );
                    $fileSystem->chmod($gitCommitMsgFilename, 0755);
                }

                $this->getLogger()->debug('Config created', ['file' => $vendorConfigFilename]);
                $this->getLogger()->debug('Pre commit hook created', ['file' => $gitPreCommitFilename]);
                $this->getLogger()->debug('Commit msg hook created', ['file' => $gitCommitMsgFilename]);
            } elseif (self::ACTION_DROP == $action) {
                if (!$optionDryRun) {
                    $fileSystem->remove([$gitCommitMsgFilename, $gitPreCommitFilename, $vendorConfigFilename]);
                }

                $this->getLogger()->debug('Config removed', ['file' => $vendorConfigFilename]);
                $this->getLogger()->debug('Pre commit hook removed', ['file' => $gitPreCommitFilename]);
                $this->getLogger()->debug('Commit msg hook removed', ['file' => $gitCommitMsgFilename]);
            }
        }

        if ($prettyOutput) {
            $progress->setMessage("Done", 'filename');
            $progress->finish();

            if (self::ACTION_INIT == $action) {
                $this->getSymfonyStyle()->success('GrumPHP is sniffing your vendors code!');
            } elseif (self::ACTION_DROP == $action) {
                $this->getSymfonyStyle()->note('GrumPHP stopped sniffing your vendors commits! Too bad ...');
            }
        }

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }

    /**
     * @param $path
     * @return mixed|string
     */
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

    /**
     * @param $path
     * @return mixed|string
     */
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
