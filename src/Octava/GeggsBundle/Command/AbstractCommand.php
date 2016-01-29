<?php
namespace Octava\GeggsBundle\Command;

use Monolog\Processor\MemoryPeakUsageProcessor;
use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Helper\RepositoryFactory;
use Octava\GeggsBundle\Helper\RepositoryList;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->logger = new Logger($this->getName());
        $this->logger->pushHandler(new ConsoleHandler($output));
        $this->logger->pushProcessor(new MemoryPeakUsageProcessor());

        $this->symfonyStyle = new SymfonyStyle($input, $output);
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
}
