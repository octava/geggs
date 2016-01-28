<?php
namespace Octava\GeggsBundle\Helper;

use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class RepositoryList
 * @package Octava\GeggsBundle\Helper
 */
class RepositoryList
{
    /**
     * @var RepositoryModel[]
     */
    protected $vendorModels;
    /**
     * @var RepositoryModel
     */
    protected $projectModel;

    /**
     * RepositoryList constructor.
     * @param RepositoryModel   $projectModel
     * @param RepositoryModel[] $vendors
     */
    public function __construct(RepositoryModel $projectModel, array $vendors)
    {
        $this->projectModel = $projectModel;
        $this->vendorModels = $vendors;
    }

    /**
     * @return \Octava\GeggsBundle\Model\RepositoryModel[]
     */
    public function getVendorModels()
    {
        return $this->vendorModels;
    }

    /**
     * @return RepositoryModel
     */
    public function getProjectModel()
    {
        return $this->projectModel;
    }

    /**
     * @return RepositoryModel[]
     */
    public function getAll()
    {
        return [$this->projectModel] + $this->vendorModels;
    }
}
