<?php
namespace Octava\GeggsBundle\Command;

use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Plugin\AbstractPlugin;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractCommand
 * @package Octava\GeggsBundle\Command
 */
class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var Logger
     */
    private $logger;


    protected function configure()
    {
        throw new \BadMethodCallException('Method "configure" not implemented');
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

        $list = $this->getRepositoryModelList();
        $plugins = $this->getPlugins();

        foreach ($plugins as $plugin) {
            $plugin->execute($list);

            if ($plugin->isPropagationStopped()) {
                $this->getLogger()->notice('Plugin isPropagationStopped', ['plugin' => get_class($plugin)]);
                break;
            }
        }

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->logger = new Logger($this->getName());
        $this->logger->pushHandler(new ConsoleHandler($output));
        $this->logger->pushProcessor(new MemoryPeakUsageProcessor());
        if (!empty($this->getConfig()->getLogFilename())) {
            $this->logger->pushHandler(new StreamHandler($this->getConfig()->getLogFilename()));
        }

        $this->symfonyStyle = new SymfonyStyle($input, $output);

        $this->getLogger()->debug('Config file', ['file' => $this->getApplication()->getConfigDefaultPath()]);
    }

    /**
     * @return RepositoryList
     */
    protected function getRepositoryModelList()
    {
        $factory = new RepositoryFactory($this->getConfig(), $this->getLogger());

        return $factory->buildRepositoryModelList();
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return SymfonyStyle
     */
    protected function getSymfonyStyle()
    {
        return $this->symfonyStyle;
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->getContainer()->get('octava_geggs.config');
    }

    /**
     * @return AbstractPlugin[]
     */
    protected function getPlugins()
    {
        $plugins = $this->getConfig()->getPlugins($this->getName());

        $result = [];

        foreach ($plugins as $class) {
            $r = new \ReflectionClass($class);
            if ($r->isSubclassOf('Octava\\GeggsBundle\\Plugin\\AbstractPlugin')
                && !$r->isAbstract()
            ) {
                /** @var \Octava\GeggsBundle\Plugin\AbstractPlugin $plugin */
                $plugin = $r->newInstance($this->getConfig(), $this->getSymfonyStyle(), $this->getLogger());
                $result[] = $plugin;
            }
        }

        return $result;
    }
}
