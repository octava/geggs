<?php
namespace Octava\Geggs\Command;

use Octava\Geggs\DependencyInjection\OctavaGeggsExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getOption('config-file');
        if (!file_exists($filename)) {
            throw new \RuntimeException(sprintf('Config file "%s" not found', $filename));
        }

        //http://symfony.com/doc/2.8/components/dependency_injection/compilation.html
        $container = $this->getContainer();

        $extension = new OctavaGeggsExtension();
        $container->registerExtension($extension);

        $loader = new YamlFileLoader($container, new FileLocator(dirname($filename)));
        $loader->load(basename($filename));

        $container->compile();
    }
}
