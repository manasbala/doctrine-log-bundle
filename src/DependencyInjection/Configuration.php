<?php

namespace Mb\DoctrineLogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Mb\DoctrineLogBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
	    $treeBuilder = new TreeBuilder('mb_doctrine_log');
	    $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('ignore_properties')->prototype('scalar')->end()
            ->end()
            ->scalarNode('entity_manager')
                ->defaultValue('default')
            ->end()
            ->scalarNode('listener_class')
                ->defaultValue('Mb\DoctrineLogBundle\EventListener\Logger')
            ->end()
        ;

        return $treeBuilder;
    }
}
