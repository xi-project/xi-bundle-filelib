<?php

namespace Xi\Bundle\FilelibBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Xi\Bundle\FilelibBundle\DependencyInjection\Compiler\EventListenerPass;
use Xi\Bundle\FilelibBundle\DependencyInjection\Compiler\ProfilePass;
use Xi\Bundle\FilelibBundle\DependencyInjection\Compiler\PluginPass;

class XiFilelibBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ProfilePass());
        $container->addCompilerPass(new PluginPass());
        $container->addCompilerPass(new EventListenerPass());
    }

}

