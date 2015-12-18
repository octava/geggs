<?php
namespace Octava\Geggs\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AbstractContainerCommand
 * @package Octava\Geggs\Command
 */
abstract class AbstractContainerCommand extends Command
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * AbstractContainerCommand constructor.
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
        parent::__construct(null);
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }
}
