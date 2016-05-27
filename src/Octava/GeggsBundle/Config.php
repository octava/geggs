<?php
namespace Octava\GeggsBundle;

use Octava\GeggsBundle\Plugin\AbstractPlugin;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Config
 * @package Octava\GeggsBundle
 */
class Config
{
    /**
     * @var string
     */
    protected $bin = 'git';

    protected $mainDir = '.';

    protected $vendorDirs = null;

    protected $commands = [];

    protected $config;

    /**
     * Config constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->bin = $config['bin'];
        $this->mainDir = realpath($config['dir']['main']);
        $this->commands = $config['commands'];
    }

    /**
     * @return string
     */
    public function getGitBin()
    {
        return $this->bin['git'];
    }

    /**
     * @return string
     */
    public function getComposerBin()
    {
        return $this->bin['composer'];
    }

    /**
     * @return string
     */
    public function getMainDir()
    {
        return $this->mainDir;
    }

    /**
     * @return array
     */
    public function getVendorDirs()
    {
        if (null === $this->vendorDirs) {
            $this->initVendorDirs($this->config['dir']['vendors']);
        }

        return $this->vendorDirs;
    }

    /**
     * Tries to make a path relative to the project, which prints nicer.
     *
     * @param string $absolutePath
     * @return string
     */
    public function makePathRelative($absolutePath)
    {
        return str_replace($this->getMainDir().'/', '', realpath($absolutePath) ?: $absolutePath);
    }

    /**
     * @return string
     */
    public function getLogFilename()
    {
        $filename = null;
        if (!empty($this->config['log_file'])) {
            $filename = $this->config['log_file'];
            $fileSystem = new Filesystem();
            $filename = $fileSystem->isAbsolutePath($filename) ?: $this->getMainDir().DIRECTORY_SEPARATOR.$filename;
        }

        return $filename;
    }

    /**
     * Return plugins from config
     * @param string $commandName
     * @return AbstractPlugin[]
     */
    public function getPlugins($commandName)
    {
        $name = str_replace('-', '_', $commandName);
        $result = [];
        if (array_key_exists($name, $this->commands)) {
            $result = $this->commands[$name];
        }

        return $result;
    }

    private function initVendorDirs(array $dirs)
    {
        $this->vendorDirs = [];
        $fileSystem = new Filesystem();

        foreach ($dirs as $dir) {
            $dir = $fileSystem->isAbsolutePath($dir) ?: $this->getMainDir().DIRECTORY_SEPARATOR.$dir;
            if (!$fileSystem->exists($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" does not exists', $dir));
            }

            $data = array_merge(glob($dir.'/*/.git', GLOB_ONLYDIR), glob($dir.'/.git', GLOB_ONLYDIR));
            foreach ($data as $item) {
                $this->vendorDirs[] = dirname($item);
            }
        }
    }
}
