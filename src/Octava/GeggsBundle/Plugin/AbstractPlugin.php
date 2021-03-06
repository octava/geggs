<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Helper\LoggerTrait;
use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractPlugin
 * @package Octava\GeggsBundle\Plugin
 */
abstract class AbstractPlugin
{
    use LoggerTrait;

    /**
     * @var bool
     */
    private $isPropagationStopped = false;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var Input
     */
    private $input;

    /**
     * @param Config       $config
     * @param SymfonyStyle $io
     * @param Logger       $logger
     */
    public function __construct(Config $config, SymfonyStyle $io, Logger $logger)
    {
        $this->config = $config;
        $this->symfonyStyle = $io;
        $this->setLogger($logger);
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return SymfonyStyle
     */
    public function getSymfonyStyle()
    {
        return $this->symfonyStyle;
    }

    /**
     * @param RepositoryList $repositories
     */
    abstract public function execute(RepositoryList $repositories);

    /**
     * @return $this
     */
    public function stopPropagation()
    {
        $this->isPropagationStopped = true;

        $this->getLogger()->debug('Stop Propagation', ['class' => get_called_class()]);

        return $this;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->isPropagationStopped;
    }

    /**
     * @return Input
     */
    public function getInput()
    {
        if (!$this->input) {
            $reflection = new \ReflectionClass($this->symfonyStyle);
            $property = $reflection->getProperty('input');
            $property->setAccessible(true);

            $this->input = $property->getValue($this->symfonyStyle);

            $property->setAccessible(false);
        }

        return $this->input;
    }

    /**
     * @return bool
     */
    public function isDryRun()
    {
        return $this->getInput()->hasOption('dry-run') && $this->getInput()->getOption('dry-run');
    }

    /**
     * @param RepositoryModel[] $repositories
     * @return array(RepositoryModel, RepositoryModel[]))
     */
    protected function getRepositories(array $repositories)
    {
        /** @var RepositoryModel $rootRepository */
        $rootRepository = null;
        /** @var RepositoryModel[] $vendors */
        $vendors = [];
        foreach ($repositories as $repository) {
            if ($repository->getType() === RepositoryModel::TYPE_VENDOR) {
                $vendors[] = $repository;
            } else {
                $rootRepository = $repository;
            }
        }

        return [$rootRepository, $vendors];
    }
}
