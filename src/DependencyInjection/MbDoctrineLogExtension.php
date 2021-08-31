<?php

namespace Mb\DoctrineLogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class MbDoctrineLogExtension
 * @package Mb\DoctrineLog\DependencyInjection
 */
class MbDoctrineLogExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $emName = sprintf('doctrine.orm.%s_entity_manager', $config['entity_manager']);
        $emReference = new Reference($emName);
        $definition = $container->register('mb_doctrine_log.event_listener.logger', $config['listener_class']);

        $definition->setArgument(0, $emReference);
        $definition->setArgument(4, $config['ignore_properties']);
    }
}

