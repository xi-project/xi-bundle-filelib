<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;

/**
 * FilelibExtension
 *
 */
class XiFilelibExtension extends Extension
{
    /**
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $filelib = $container->getDefinition('xi_filelib');
        $filelib->addMethodCall('setTempDir', array($config['temp_dir']));

        $container->getDefinition('xi_filelib.publisher')->addMethodCall(
            'attachTo',
            array(new Reference('xi_filelib'))
        );

        $storage = $container->getDefinition('xi_filelib.storage');
        $storage->replaceArgument(0, $config['storage']['root']);
        $storage->replaceArgument(2, $config['storage']['file_permission']);
        $storage->replaceArgument(3, $config['storage']['directory_permission']);


        if ($config['storage']['directory_id_calculator']) {

            $calc = $container->getDefinition('xi_filelib.storage.directory_id_calculator');

            $calc->setClass($config['storage']['directory_id_calculator']['class']);
            $calc->setArguments($config['storage']['directory_id_calculator']['arguments']);
        }

        // profiles
        foreach ($config['profiles'] as $profileName) {
            $profileKey = "xi_filelib.profiles.{$profileName}";
            $definition = new Definition('Xi\Filelib\Profile\FileProfile', array($profileName));
            $container->setDefinition($profileKey, $definition);
            $filelib->addMethodCall('addProfile', array(new Reference($profileKey)));
        }

        // plugins
        if (isset($config['plugins'])) {
            foreach ($config['plugins'] as $identifier => $pluginConf) {
                $definition = new Definition($pluginConf['class'], $pluginConf['arguments']);
                if (isset($pluginConf['calls'])) {
                    $definition->setMethodCalls($pluginConf['calls']);
                }
                $pluginName = 'xi_filelib.plugins.' . $identifier;
                $container->setDefinition($pluginName, $definition);
                $filelib->addMethodCall('addPlugin', array(new Reference($pluginName), $pluginConf['profiles']));
            }
        }

        $renderer = $container->getDefinition('xi_filelib.renderer');
        $renderer->addMethodCall('enableAcceleration', array($config['renderer']['enable_acceleration']));
        $renderer->addMethodCall('stripPrefixFromPath', array($config['renderer']['strip_prefix']));
        $renderer->addMethodCall('addPrefixToPath', array($config['renderer']['add_prefix']));

        $twig = $container->getDefinition('xi_filelib.twig.extension');
        $twig->addArgument($config['twig']['not_found_url']);

        if ($config['publisher']['beautifurls'] == false) {
            $container->removeDefinition('xi_filelib.slugifier');
            $container->removeDefinition('xi_filelib.slugifier_adapter');
            $container->removeDefinition('xi_filelib.slugigier_pretransliterator');
            $container->removeDefinition('xi_filelib.transliterator');
            $linker = $container->getDefinition('xi_filelib.publisher.linker');
            $linker->setClass('Xi\Filelib\Publisher\Linker\SequentialLinker');
            $linker->setArguments(array());
        }

        if ($config['publisher']['adapter']) {
            $adapter = $container->getDefinition('xi_filelib.publisher.adapter');
            $adapter->setClass($config['publisher']['adapter']['class']);
            $adapter->setArguments($config['publisher']['adapter']['arguments']);
        }

        if ($config['queue_adapter_service']) {
            $filelib->addMethodCall('createQueueFromAdapter', array(new Reference($config['queue_adapter_service'])));
        }

        if ($config['cache_adapter_service']) {
            $filelib->addMethodCall('createCacheFromAdapter', array(new Reference($config['cache_adapter_service'])));
        }


        if ($config['authorization']['enabled'] === true) {

            if ($config['authorization']['adapter_service']) {
                $container->setAlias('xi_filelib.authorization.adapter', $config['authorization']['adapter_service']);
            }
            $filelib->addMethodCall('addPlugin', array(new Reference('xi_filelib.authorization.plugin')));

            if ($config['publisher']['automatic_publisher']) {
                $filelib->addMethodCall('addPlugin', array(new Reference('xi_filelib.publisher.automatic_publisher_plugin')));
            } else {
                $container->removeDefinition('xi_filelib.publisher.automatic_publisher_plugin');
            }

        } else {
            $container->removeDefinition('xi_filelib.authorization.adapter');
            $container->removeDefinition('xi_filelib.authorization.plugin');
            $container->removeDefinition('xi_filelib.publisher.automatic_publisher_plugin');
        }
    }
}
