<?php

namespace Goksagun\ElasticApmBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('elastic_apm');

        $rootNode
            ->children()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->end()
            ->children()
            ->scalarNode('appName')->isRequired()->end()
            ->end()
            ->children()
            ->scalarNode('appVersion')->defaultValue('')->end()
            ->end()
            ->children()
            ->scalarNode('serverUrl')->defaultValue('http://127.0.0.1:8200')->end()
            ->end()
            ->children()
            ->scalarNode('secretToken')->defaultNull()->end()
            ->end()
            ->children()
            ->integerNode('timeout')->defaultValue(5)->end()
            ->end();

        return $treeBuilder;
    }
}