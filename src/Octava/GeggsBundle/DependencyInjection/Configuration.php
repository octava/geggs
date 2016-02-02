<?php

namespace Octava\GeggsBundle\DependencyInjection;

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
            ->arrayNode('bin')
            ->children()
                ->scalarNode('git')->defaultValue('git')->cannotBeEmpty()->end()
                ->scalarNode('composer')->defaultValue('composer')->cannotBeEmpty()->end()
            ->end()
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
                ->arrayNode('commands')
                    ->requiresAtLeastOneElement()
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                            ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
