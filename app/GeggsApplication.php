<?php
use Octava\Geggs\Command\StatusCommand;
use Octava\Geggs\DependencyInjection\OctavaGeggsExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;

class GeggsApplication extends Application
{
    const APP_NAME = 'Geggs';

    const APP_CONFIG_FILE = 'geggs.yml';

    /**
     * @var string
     */
    protected $configDefaultPath;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Set up application:
     */
    public function __construct()
    {
        parent::__construct(self::APP_NAME, trim(file_get_contents(dirname(__DIR__).'/version')));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Path to config',
                $this->getConfigDefaultPath()
            )
        );

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $container = $this->getContainer();
        $commands = parent::getDefaultCommands();

        $commands[] = new StatusCommand($container);

        return $commands;
    }

    /**
     * @return string
     */
    protected function getConfigDefaultPath()
    {
        if (!$this->configDefaultPath) {
            $composerFile = 'composer.json';
            if (file_exists($composerFile)) {
                $composer = json_decode(file_get_contents($composerFile), true);
                if (isset($composer['extra']['geggs']['config-default-path'])) {
                    $this->configDefaultPath = $composer['extra']['geggs']['config-default-path'];
                }
            }

            if (!file_exists($this->configDefaultPath)) {
                $this->configDefaultPath = getcwd().DIRECTORY_SEPARATOR.self::APP_CONFIG_FILE;

                if (!file_exists($this->configDefaultPath)) {
                    $this->configDefaultPath = GEGGS_PATH.DIRECTORY_SEPARATOR.self::APP_CONFIG_FILE;
                }
            }
        }

        return $this->configDefaultPath;
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        // Load cli options:
        $input = new ArgvInput();
        $configPath = $input->getParameterOption(['--config', '-c'], $this->getConfigDefaultPath());

        // Make sure to set the full path when it is declared relative
        // This will fix some issues in windows.
        $filesystem = new Filesystem();
        if (!$filesystem->isAbsolutePath($configPath)) {
            $configPath = getcwd().DIRECTORY_SEPARATOR.$configPath;
        }

        $this->container = new ContainerBuilder();
        $extension = new OctavaGeggsExtension();
        $this->container->registerExtension($extension);

        $loader = new YamlFileLoader($this->container, new FileLocator(dirname($configPath)));
        $loader->load(basename($configPath));

        $this->container->compile();

        return $this->container;
    }
}
