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


        // profiles
        foreach ($config['profiles'] as $profileName) {
            $profileKey = "xi_filelib.profiles.{$profileName}";
            $definition = new Definition('Xi\Filelib\File\FileProfile', array($profileName));
            $definition->addTag('xi_filelib.profile');
            $container->setDefinition($profileKey, $definition);
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

        if ($config['publisher']['beautifurls'] == false) {
            $linker = $container->getDefinition('xi_filelib.publisher.linker');
            $linker->setClass('Xi\Filelib\Publisher\Linker\SequentialLinker');
            $linker->setArguments(array());
        }


        return;


        $this->loadBackend($config['backend'], $container);

        // Storage

        // Dir id calc
        $definition = new Definition($config['storage_filesystem']['directoryIdCalculator']['type'], array($config['storage_filesystem']['directoryIdCalculator']['options']));
        $container->setDefinition('xi_filelib.storage.directoryIdCalculator', $definition);

        // Storage
        $definition = new Definition('Xi\Filelib\Storage\FilesystemStorage', array(array(
            'directoryPermission' => $config['storage_filesystem']['directoryPermission'],
            'filePermission' => $config['storage_filesystem']['filePermission'],
            'root' => $config['storage_filesystem']['root'],
        )));
        $container->setDefinition('xi_filelib.storage', $definition);
        $definition->addMethodCall('setDirectoryIdCalculator', array(
            new Reference('xi_filelib.storage.directoryIdCalculator'),
        ));

        // Publisher

        $definition = new Definition($config['publisher']['type'], array($config['publisher']['options']));
        $container->setDefinition('xi_filelib.publisher', $definition);

        // Profiles

        $pc = $config['profiles'];

        foreach ($pc as $p) {
            if ($p['linker']['type'] === 'Xi\Filelib\Linker\BeautifurlLinker') {
                $definition = new Definition($p['linker']['type'], array(
                    new Reference('xi_filelib.folderoperator'),
                    new Reference('xi_filelib.slugifier'),
                    $p['linker']['options']
                ));
            } else {
                $definition = new Definition($p['linker']['type'], array(
                    $p['linker']['options'],
                ));
            }

            $container->setDefinition("xi_filelib.profiles.{$p['identifier']}.linker", $definition);

            $definition = new Definition('Xi\Filelib\File\FileProfile', array(
                array(
                    'identifier' => $p['identifier'],
                    'description' => $p['description'],
                    'accessToOriginal' => $p['accessToOriginal'],
                    'publishOriginal' => $p['publishOriginal'],
                ),
            ));

            $definition->addMethodCall('setLinker', array(
                new Reference("xi_filelib.profiles.{$p['identifier']}.linker")
            ));
            $definition->addTag('xi_filelib.profile');

            $container->setDefinition("xi_filelib.profiles.{$p['identifier']}", $definition);
        }

        if (isset($config['plugins'])) {
            $this->loadPlugins($config['plugins'], $container, $config);
        }

        // If acl resource is defined, use alias. Otherwise define simple acl.
        if ($config['acl']) {
            $alias = new Alias('xi_filelib.acl');
            $container->setAlias($alias, $config['acl']);
        } else {
            $definition = new Definition('Xi\Filelib\Acl\SimpleAcl');
            $container->setDefinition('xi_filelib.acl', $definition);
        }

        // $edDefinition = new Definition('Symfony\Component\EventDispatcher\EventDispatcher');
        // $container->setDefinition('xi_filelib.eventdispatcher', $edDefinition);

        $eddDefinition = new Alias('xi_filelib.eventdispatcher');
        $container->setAlias($eddDefinition, 'event_dispatcher');

        // Main

        $definition = new Definition('Xi\Filelib\FileLibrary');
        $container->setDefinition('xi_filelib', $definition);

        $definition->addMethodCall('setEventDispatcher', array(
            new Reference('xi_filelib.eventdispatcher')
        ));

        $definition->addMethodCall('setTempDir', array(
            $config['tempDir']
        ));

        $definition->addMethodCall('setPlatform', array(
            new Reference('xi_filelib.backend.platform'),
        ));

        $definition->addMethodCall('setBackend', array(
            new Reference('xi_filelib.backend'),
        ));


        $definition->addMethodCall('setStorage', array(
            new Reference('xi_filelib.storage'),
        ));

        $definition->addMethodCall('setPublisher', array(
            new Reference('xi_filelib.publisher'),
        ));

        $definition->addMethodCall('setPublisher', array(
            new Reference('xi_filelib.publisher'),
        ));

        $definition->addMethodCall('setAcl', array(
            new Reference('xi_filelib.acl'),
        ));

        $definition->addMethodCall('setFileOperator', array(
            new Reference('xi_filelib.fileoperator')
        ));

        $definition->addMethodCall('setFolderOperator', array(
            new Reference('xi_filelib.folderoperator')
        ));

        if (isset($config['queue']) && $config['queue']) {
            $queueDefinition = new Definition($config['queue']['type'], $config['queue']['arguments']);
            $container->setDefinition('xi_filelib.queue', $queueDefinition);
            $definition->addMethodCall('setQueue', array(new Reference('xi_filelib.queue')));
        }

        if (isset($config['transliterator']) && $config['transliterator']) {
            $translitDefinition = new Definition($config['transliterator']['type'], $config['transliterator']['arguments']);
            $container->setDefinition('xi_filelib.transliterator', $translitDefinition);
        }

        if (isset($config['slugifier']) && $config['slugifier']) {
            $slugDefinition = new Definition($config['slugifier']['type']);
            $slugDefinition->addArgument(new Reference('xi_filelib.transliterator'));
            $container->setDefinition('xi_filelib.slugifier', $slugDefinition);
        }

    }
}
