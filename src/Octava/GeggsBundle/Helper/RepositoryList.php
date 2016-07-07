<?php
namespace Octava\GeggsBundle\Helper;

use Octava\GeggsBundle\Model\RepositoryModel;

/**
 * Class RepositoryList
 * @package Octava\GeggsBundle\Helper
 */
class RepositoryList implements \Countable
{
    /**
     * @var RepositoryModel[]
     */
    protected $vendorModels = [];
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
        $result = $this->vendorModels;
        array_unshift($result, $this->projectModel);

        return $result;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        $result = count($this->getVendorModels()) + 1;

        return $result;
    }
}
