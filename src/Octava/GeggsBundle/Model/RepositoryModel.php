<?php
namespace Octava\GeggsBundle\Model;

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
     * RepositoryModel constructor.
     * @param string $type
     * @param string $rootPath
     * @param string $path
     */
    public function __construct($type, $rootPath, $path)
    {
        $this->type = $type;
        $this->rootPath = $rootPath;
        $this->absolutePath = $path;
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
}
