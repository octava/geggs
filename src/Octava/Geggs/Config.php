<?php
namespace Octava\Geggs;

/**
 * Class Config
 * @package Octava\Geggs
 */
class Config
{
    /**
     * @var string
     */
    protected $bin = 'git';

    protected $mainDir = '.';

    protected $vendorDirs = [];

    protected $plugins = [];

    /**
     * Config constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->bin = $config['bin'];
        $this->mainDir = realpath($config['dir']['main']);
    }

    /**
     * @return string
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * @return string
     */
    public function getMainDir()
    {
        return $this->mainDir;
    }
}
