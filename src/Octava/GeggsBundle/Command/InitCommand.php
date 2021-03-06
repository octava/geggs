<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class InitCommand
 * @package Octava\GeggsBundle\Command
 */
class InitCommand extends AbstractCommand
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $skeletonDirs;

    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Init geggs environment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug('Start', ['command_name' => $this->getName()]);

        $configDir = getcwd().'/.geggs';

        $this->getSymfonyStyle()->writeln('Initiating managing process for application with <comment>Geggs</comment>');

        $exitCode = 0;
        // Check if there is already a config dir
        if (file_exists($configDir)) {
            $this->getSymfonyStyle()->warning('Already exists .geggs directory.');
        } else {
            /** @var GeggsApplication $application */
            $application = $this->getApplication();
            $source = implode(
                DIRECTORY_SEPARATOR,
                [
                    $application->getBundleDir(),
                    'Resources',
                    'skeleton',
                    'geggs',
                ]
            );
            $this->skeletonDirs = [$source];

            try {
                $finder = new Finder();
                $finder->files()->in($source);
                foreach ($finder as $file) {
                    /** @var \Symfony\Component\Finder\SplFileInfo $file */

                    $targetFilename = $configDir.DIRECTORY_SEPARATOR.$file->getRelativePathname();

                    if (!$input->getOption('dry-run')) {
                        $this->renderFile($file->getRelativePathname(), $targetFilename);
                    }

                    $this->getLogger()->debug('Copy file', ['source' => $file->getPath(), 'target' => $targetFilename]);
                }

                $this->getSymfonyStyle()->success(
                    'The configuration for Geggs has been generated at .geggs directory.'
                );
                $this->getSymfonyStyle()->note('Please!! Review and adjust the configuration.');

            } catch (\Exception $e) {
                $this->getSymfonyStyle()->error('Unable to generate the configuration.');
                $this->getSymfonyStyle()->error($e->getMessage());

                $exitCode = 50;
            }
        }

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);

        return $exitCode;
    }

    protected function renderFile($template, $target)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        return file_put_contents($target, $template);
    }
}
