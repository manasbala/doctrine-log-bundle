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
	    if (method_exists($treeBuilder, 'getRootNode')) {
		    $rootNode = $treeBuilder->getRootNode();
	    } else {
		    // for symfony/config 4.1 and older
		    $rootNode = $treeBuilder->root('mb_doctrine_log');
	    }

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
