<?php
namespace Octava\GeggsBundle\Plugin;


use Octava\GeggsBundle\Model\RepositoryModel;

abstract class AbstractPlugin
{
    /**
     * @var bool
     */
    protected $isPropagationStopped = false;

    /**
     * @param RepositoryModel[] $repositories
     */
    abstract public function execute(array $repositories);

    /**
     * @return $this
     */
    public function stopPropagation()
    {
        $this->isPropagationStopped = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->isPropagationStopped;
    }
}
