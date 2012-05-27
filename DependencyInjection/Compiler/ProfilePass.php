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
 * Adds profiles to filelib
 */
class ProfilePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('filelib.profile');
        $filelib = $container->getDefinition('filelib');
        foreach ($services as $service => $params) {
            $filelib->addMethodCall('addProfile', array(new Reference($service)));
        }
    }

}
