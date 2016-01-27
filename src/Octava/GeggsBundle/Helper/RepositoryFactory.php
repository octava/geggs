<?php
namespace Octava\GeggsBundle\Helper;

use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Git\Status;
use Octava\GeggsBundle\Model\RepositoryModel;
use Octava\GeggsBundle\Provider\GitProvider;

/**
 * Class RepositoryFactory
 * @package Octava\GeggsBundle\Helper
 */
class RepositoryFactory
{
    /**
     * @var Config
     */
    protected $config;


    /**
     * RepositoryFactory constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return RepositoryModel[]
     */
    public function buildRepositoryModelList()
    {
        $path = $this->config->getMainDir();

        $provider = new GitProvider($this->config, $path);
        $model = new RepositoryModel(RepositoryModel::TYPE_ROOT, $path, $path, $provider);
        $result[] = $model;

        foreach ($this->config->getVendorDirs() as $path) {
            $provider = new GitProvider($this->config, $path);
            $model = new RepositoryModel(RepositoryModel::TYPE_VENDOR, $this->config->getMainDir(), $path, $provider);
            $result[] = $model;
        }

        return $result;
    }
}
