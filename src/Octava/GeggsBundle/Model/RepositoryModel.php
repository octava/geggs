<?php
namespace Octava\GeggsBundle\Model;

use Octava\GeggsBundle\Provider\AbstractProvider;
use ReflectionMethod;

/**
 * Class RepositoryModel
 * @package Octava\GeggsBundle\Model
 */
class RepositoryModel
{
    const TYPE_ROOT = 'root';

    const TYPE_VENDOR = 'vendor';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $absolutePath;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var AbstractProvider
     */
    protected $provider;

    /**
     * RepositoryModel constructor.
     * @param string $type
     * @param string $rootPath
     * @param string $path
     * @param AbstractProvider $provider
     */
    public function __construct($type, $rootPath, $path, AbstractProvider $provider)
    {
        $this->type = $type;
        $this->rootPath = $rootPath;
        $this->absolutePath = $path;
        $this->provider = $provider;
    }

    /**
     * @return AbstractProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getPath();
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Result of `git status` command
     * @return string
     */
    public function getRawStatus()
    {

    }

    /**
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * Relative path
     * @return string
     */
    public function getPath()
    {
        return substr($this->absolutePath, strlen($this->rootPath) + 1);
    }

    /**
     * @example symfony/symfony
     * @return string
     */
    public function getPackageName()
    {

    }

    /**
     * @example dev-master
     * @return string
     */
    public function getVersion()
    {

    }

    /**
     * @return string
     */
    public function dump()
    {
        $result = [];

        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (0 === strpos($method->getName(), 'get')) {
                $propertyName = lcfirst(substr($method->getName(), 3));
                $result[$propertyName] = $method->invoke($this);
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        $result = $this->getProvider()->run('rev-parse', ['--abbrev-ref', 'HEAD']);

        return $result;
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        $raw = $this->getProvider()->run('status', ['--porcelain']);

        return !empty($raw);
    }
}
