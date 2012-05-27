<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;

/**
 * FilelibExtension
 *
 */
class XiFilelibExtension extends Extension
{

    /**
     * Loads the Monolog configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Backend

        $backend = new Definition($config['backend']['type'], array(new Reference($config['backend']['key'])));

        $container->setDefinition('filelib.backend', $backend);

        // @todo: dirty quick kludge to make d-porssi work. How to actually do?!?!?!? Must... investigate... Doctrine f.ex
        // $backend->addMethodCall($config['backend']['method'], array(new Reference($config['backend']['key'])));

        if (isset($config['backend']['folderEntity'])) {
           $backend->addMethodCall('setFolderEntityName', array($config['backend']['folderEntity']));
        }

        if (isset($config['backend']['fileEntity'])) {
            $backend->addMethodCall('setFileEntityName', array($config['backend']['fileEntity']));
        }


        // Storage

        // Dir id calc
        $definition = new Definition($config['storage_filesystem']['directoryIdCalculator']['type'], array($config['storage_filesystem']['directoryIdCalculator']['options']));
        $container->setDefinition('filelib.storage.directoryIdCalculator', $definition);

        // Storage
        $definition = new Definition('Xi\Filelib\Storage\FilesystemStorage', array(array(
            'directoryPermission' => $config['storage_filesystem']['directoryPermission'],
            'filePermission' => $config['storage_filesystem']['filePermission'],
            'root' => $config['storage_filesystem']['root'],
        )));
        $container->setDefinition('filelib.storage', $definition);
        $definition->addMethodCall('setDirectoryIdCalculator', array(
            new Reference('filelib.storage.directoryIdCalculator'),
        ));


        // Publisher

        $definition = new Definition($config['publisher']['type'], array($config['publisher']['options']));
        $container->setDefinition('filelib.publisher', $definition);

        // Profiles


        $pc = $config['profiles'];

        foreach ($pc as $p) {

            $definition = new Definition($p['linker']['type'], array(
                $p['linker']['options'],
            ));
            $container->setDefinition("filelib.profiles.{$p['identifier']}.linker", $definition);

            $definition = new Definition('Xi\Filelib\File\FileProfile', array(
                array(
                    'identifier' => $p['identifier'],
                    'description' => $p['description'],
                ),
            ));

            $definition->addMethodCall('setLinker', array(
                new Reference("filelib.profiles.{$p['identifier']}.linker")
            ));
            $definition->addTag('filelib.profile');

            $container->setDefinition("filelib.profiles.{$p['identifier']}", $definition);
        }

        foreach ($config['plugins'] as $pluginOptions)
        {
            if (!isset($pluginOptions['profiles'])) {
                $pluginOptions['profiles'] = array_keys($this->configuration->getProfiles());
            }

            $definition = new Definition($pluginOptions['type'], array(
                $pluginOptions,
            ));
            $definition->addTag('filelib.plugin');
            $container->setDefinition("filelib.plugins.{$pluginOptions['identifier']}", $definition);
        }

        // If acl resource is defined, use alias. Otherwise define simple acl.
        if ($config['acl']) {
            $alias = new Alias('filelib.acl');
            $container->setAlias($alias, $config['acl']);
        } else {
            $definition = new Definition('Xi\Filelib\Acl\SimpleAcl');
            $container->setDefinition('filelib.acl', $definition);
        }

        $eventDispatcher = new Definition('Symfony\Component\EventDispatcher\EventDispatcher');
        $container->setDefinition('filelib.eventDispatcher', $eventDispatcher);

        // Main

        $definition = new Definition('Xi\Filelib\FileLibrary');
        $container->setDefinition('filelib', $definition);

        $definition->addMethodCall('setEventDispatcher', array(
            new Reference('filelib.eventdispatcher')
        ));

        $definition->addMethodCall('setTempDir', array(
            $config['tempDir']
        ));

        // Set backend
        $definition->addMethodCall('setBackend', array(
            new Reference('filelib.backend'),
        ));

        // Set backend
        $definition->addMethodCall('setStorage', array(
            new Reference('filelib.storage'),
        ));

        $definition->addMethodCall('setPublisher', array(
            new Reference('filelib.publisher'),
        ));

        $definition->addMethodCall('setPublisher', array(
            new Reference('filelib.publisher'),
        ));


        $definition->addMethodCall('setAcl', array(
            new Reference('filelib.acl'),
        ));

        $definition->addMethodCall('setFileOperator', array(
            new Reference('filelib.fileoperator')
        ));

        if (isset($config['queue']) && $config['queue']) {
            $queueDefinition = new Definition($config['queue']['type'], $config['queue']['arguments']);
            $container->setDefinition('filelib.queue', $queueDefinition);
            $definition->addMethodCall('setQueue', array(new Reference('filelib.queue')));
        }

        $definition->addMethodCall('dispatchInitEvent');

        $definition = new Definition('Xi\Filelib\File\DefaultFileOperator');
        $container->setDefinition('filelib.fileoperator', $definition);
        $definition->addArgument(new Reference('filelib'));

        $definition = new Definition('Xi\Filelib\Renderer\SymfonyRenderer');
        $container->setDefinition('filelib.renderer', $definition);
        $definition->addArgument(new Reference('filelib'));
        $definition->addMethodCall('enableAcceleration', array($config['renderer']['accelerate']));

        if ($config['renderer']['stripPrefixFromAcceleratedPath']) {
            $definition->addMethodCall('setStripPrefixFromAcceleratedPath', array($config['renderer']['stripPrefixFromAcceleratedPath']));
        }

        if ($config['renderer']['addPrefixToAcceleratedPath']) {
            $definition->addMethodCall('setAddPrefixToAcceleratedPath', array($config['renderer']['addPrefixToAcceleratedPath']));
        }


    }

}
