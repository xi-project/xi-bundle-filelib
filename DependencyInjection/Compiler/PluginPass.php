<?php

namespace Xi\Bundle\FilelibBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds plugins to filelib
 */
class PluginPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('filelib.plugin');
        $filelib = $container->getDefinition('filelib');
        foreach ($services as $service => $params) {
            $filelib->addMethodCall('addPlugin', array(new Reference($service)));
        }
    }

}
