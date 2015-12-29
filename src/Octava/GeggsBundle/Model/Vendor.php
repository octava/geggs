<?php
namespace Octava\GeggsBundle\Model;

/**
 * Class Vendor
 * @package Octava\GeggsBundle\Model
 */
class Vendor
{
    protected $repositoryUrl;
    protected $targetDirectory;
    protected $name;

    /**
     * Vendor constructor.
     * @param string $targetDirectory
     * @param string $name
     * @param string $repositoryUrl
     */
    public function __construct($targetDirectory, $name, $repositoryUrl)
    {
        $this->targetDirectory = $targetDirectory;
        $this->name = trim($name);
        $this->repositoryUrl = $repositoryUrl;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return rtrim($this->targetDirectory, '/').'/'.trim($this->name, '/');
    }

    /**
     * @return string
     */
    public function getFullRepositoryUrl()
    {
        return $this->repositoryUrl.':'.$this->name;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return substr($this->name, strrpos($this->name, '/') + 1);
    }
}
