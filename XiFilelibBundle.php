<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Xi\Bundle\FilelibBundle\DependencyInjection\Compiler\EventListenerPass;
use Xi\Bundle\FilelibBundle\DependencyInjection\Compiler\ProfilePass;
use Xi\Bundle\FilelibBundle\DependencyInjection\Compiler\PluginPass;

class XiFilelibBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProfilePass());
        $container->addCompilerPass(new PluginPass());
        $container->addCompilerPass(new EventListenerPass());
    }
}
