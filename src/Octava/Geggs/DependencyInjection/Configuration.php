<?php

namespace Octava\Geggs\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('octava_geggs');

        $rootNode
            ->children()
            ->scalarNode('bin')
            ->cannotBeEmpty()
            ->defaultValue('git')
            ->info('Git binary')
            ->end()
            ->end();

        $rootNode
            ->children()
            ->arrayNode('dir')
            ->children()
            ->scalarNode('main')->defaultValue('.')->end()
            ->arrayNode('vendors')
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        $rootNode
            ->children()
            ->arrayNode('plugins')
            ->children()
            ->arrayNode('pull')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('add')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('commit')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('status')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('push')
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
