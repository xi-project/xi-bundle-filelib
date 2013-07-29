<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $services = $container->findTaggedServiceIds('xi_filelib.event_listener');
        $eventDispatcher = $container->getDefinition('event_dispatcher');

        foreach ($services as $service => $params) {
            $eventDispatcher->addMethodCall('addSubscriber', array(new Reference($service)));
        }
    }
}
