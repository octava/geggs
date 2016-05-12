<?php
namespace Octava\GeggsBundle\Helper;

use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Model\RepositoryModel;
use Octava\GeggsBundle\Provider\GitProvider;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class RepositoryFactory
 * @package Octava\GeggsBundle\Helper
 */
class RepositoryFactory
{
    use LoggerTrait;

    /**
     * @var Config
     */
    protected $config;


    /**
     * RepositoryFactory constructor.
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Config $config, Logger $logger = null)
    {
        $this->config = $config;
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * @return RepositoryList
     */
    public function buildRepositoryModelList()
    {
        $path = $this->config->getMainDir();

        $provider = new GitProvider($this->config, $path);
        $provider->setLogger($this->getLogger());
        $projectModel = new RepositoryModel(RepositoryModel::TYPE_ROOT, $path, $path, $provider);

        $vendors = [];
        foreach ($this->config->getVendorDirs() as $path) {
            $provider = new GitProvider($this->config, $path);
            $provider->setLogger($this->getLogger());
            $model = new RepositoryModel(RepositoryModel::TYPE_VENDOR, $this->config->getMainDir(), $path, $provider);
            $vendors[] = $model;
        }
        $result = new RepositoryList($projectModel, $vendors);

        return $result;
    }
}
