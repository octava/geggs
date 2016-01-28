<?php
namespace Octava\GeggsBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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

    protected $plugins = [];

    protected $generator = [];

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
        $this->generator = $config['generator'];
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
     * @return string
     */
    public function getGeneratorRepositoryUrl()
    {
        return $this->generator['repository_url'];
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

    private function initVendorDirs(array $dirs)
    {
        $this->vendorDirs = [];
        $fileSystem = new Filesystem();

        foreach ($dirs as $dir) {
            $dir = $fileSystem->isAbsolutePath($dir) ?: $this->getMainDir().DIRECTORY_SEPARATOR.$dir;
            if (!$fileSystem->exists($dir)) {
                throw new \RuntimeException('Directory "%s" does not exists', $dir);
            }

            $finder = new Finder();
            $finder
                ->ignoreVCS(false)
                ->ignoreDotFiles(false)
                ->directories()
                ->in($dir)
                ->name('.git');

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($finder as $file) {
                $this->vendorDirs[] = $file->getPath();
            }
        }
    }
}
