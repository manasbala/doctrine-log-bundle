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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mb_doctrine_log');

        $rootNode
            ->children()
                ->arrayNode('ignore_properties')->prototype('scalar')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
