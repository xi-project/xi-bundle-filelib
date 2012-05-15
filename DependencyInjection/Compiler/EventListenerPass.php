<?php

namespace Xi\Bundle\FilelibBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Registers event listeners to filelib
 */
class EventListenerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('filelib.event.listener');
        $eventDispatcher = $container->getDefinition('filelib.eventdispatcher');
        foreach ($services as $service => $params) {
            $eventDispatcher->addMethodCall('addSubscriber', array(new Reference($service)));
        }
    }

}
