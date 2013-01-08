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

        $this->loadBackend($container);
        $this->loadPlatform(
            isset($config['backend']['platform']) ? $config['backend']['platform'] : array(), $container
        );

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
            if ($p['linker']['type'] === 'Xi\Filelib\Linker\BeautifurlLinker') {
                $definition = new Definition($p['linker']['type'], array(
                    new Reference('filelib.folderoperator'),
                    new Reference('filelib.slugifier'),
                    $p['linker']['options']
                ));
            } else {
                $definition = new Definition($p['linker']['type'], array(
                    $p['linker']['options'],
                ));
            }

            $container->setDefinition("filelib.profiles.{$p['identifier']}.linker", $definition);

            $definition = new Definition('Xi\Filelib\File\FileProfile', array(
                array(
                    'identifier' => $p['identifier'],
                    'description' => $p['description'],
                    'accessToOriginal' => $p['accessToOriginal'],
                    'publishOriginal' => $p['publishOriginal'],
                ),
            ));

            $definition->addMethodCall('setLinker', array(
                new Reference("filelib.profiles.{$p['identifier']}.linker")
            ));
            $definition->addTag('filelib.profile');

            $container->setDefinition("filelib.profiles.{$p['identifier']}", $definition);
        }

        if (isset($config['plugins'])) {
            $this->loadPlugins($config['plugins'], $container, $config);
        }

        // If acl resource is defined, use alias. Otherwise define simple acl.
        if ($config['acl']) {
            $alias = new Alias('filelib.acl');
            $container->setAlias($alias, $config['acl']);
        } else {
            $definition = new Definition('Xi\Filelib\Acl\SimpleAcl');
            $container->setDefinition('filelib.acl', $definition);
        }

        // $edDefinition = new Definition('Symfony\Component\EventDispatcher\EventDispatcher');
        // $container->setDefinition('filelib.eventdispatcher', $edDefinition);

        $eddDefinition = new Alias('filelib.eventdispatcher');
        $container->setAlias($eddDefinition, 'event_dispatcher');

        // Main

        $definition = new Definition('Xi\Filelib\FileLibrary');
        $container->setDefinition('filelib', $definition);

        $definition->addMethodCall('setEventDispatcher', array(
            new Reference('filelib.eventdispatcher')
        ));

        $definition->addMethodCall('setTempDir', array(
            $config['tempDir']
        ));

        $definition->addMethodCall('setPlatform', array(
            new Reference('filelib.backend.platform'),
        ));

        $definition->addMethodCall('setBackend', array(
            new Reference('filelib.backend'),
        ));


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

        $definition->addMethodCall('setFolderOperator', array(
            new Reference('filelib.folderoperator')
        ));

        if (isset($config['queue']) && $config['queue']) {
            $queueDefinition = new Definition($config['queue']['type'], $config['queue']['arguments']);
            $container->setDefinition('filelib.queue', $queueDefinition);
            $definition->addMethodCall('setQueue', array(new Reference('filelib.queue')));
        }

        if (isset($config['transliterator']) && $config['transliterator']) {
            $translitDefinition = new Definition($config['transliterator']['type'], $config['transliterator']['arguments']);
            $container->setDefinition('filelib.transliterator', $translitDefinition);
        }

        if (isset($config['slugifier']) && $config['slugifier']) {
            $slugDefinition = new Definition($config['slugifier']['type']);
            $slugDefinition->addArgument(new Reference('filelib.transliterator'));
            $container->setDefinition('filelib.slugifier', $slugDefinition);
        }

        $definition->addMethodCall('dispatchInitEvent');

        $definition = new Definition('Xi\Filelib\File\FileOperator');
        $container->setDefinition('filelib.fileoperator', $definition);
        $definition->addArgument(new Reference('filelib'));

        // Folder operator
        $definition = new Definition('Xi\Filelib\Folder\FolderOperator');
        $container->setDefinition('filelib.folderoperator', $definition);
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

    private function loadBackend(ContainerBuilder $container)
    {
        $imDefinition = new Definition(
            'Xi\Filelib\IdentityMap\IdentityMap',
            array(
                new Reference('filelib.eventDispatcher'),
            )
        );
        $container->setDefinition('filelib.identityMap', $imDefinition);

        $backendDefinition = new Definition(
            'Xi\Filelib\Backend\Backend',
            array(
                new Reference('filelib.eventDispatcher'),
                new Reference('filelib.backend.platform'),
                new Reference('filelib.identityMap'),
            )
        );
        $container->setDefinition('filelib.backend', $backendDefinition);
    }


    /**
     * @param  array                         $backend
     * @param  ContainerBuilder              $container
     * @throws InvalidConfigurationException
     */
    private function loadPlatform(array $platform, ContainerBuilder $container)
    {
        if (isset($platform['doctrine_orm'])) {
            $definition = $this->defineDoctrineORMPlatform($platform['doctrine_orm']);
        } else if (isset($platform['mongo'])) {
            $definition = $this->defineMongoPlatform($platform['mongo']);
        } else {
            throw new InvalidConfigurationException('Platform must be configured.');
        }

        $container->setDefinition('filelib.backend.platform', $definition);
    }

    /**
     * @param  array $platform
     * @return Definition
     */
    private function defineDoctrineORMPlatform(array $platform)
    {
        $definition = new Definition('Xi\Filelib\Backend\Platform\DoctrineOrmPlatform', array(
            new Reference($platform['entity_manager'])
        ));

        if (isset($platform['folderEntity'])) {
            $definition->addMethodCall('setFolderEntityName', array($platform['folderEntity']));
        }

        if (isset($platform['fileEntity'])) {
            $definition->addMethodCall('setFileEntityName', array($platform['fileEntity']));
        }

        return $definition;
    }

    /**
     * @param  array      $platform
     * @return Definition
     */
    private function defineMongoPlatform(array $platform)
    {
        $mongo = new Definition('Mongo', array($platform['connection']));
        $mongoDb = new Definition('MongoDB', array($mongo, $platform['database']));

        return new Definition('Xi\Filelib\Backend\Platform\MongoPlatform', array(
            $mongoDb
        ));
    }

    /**
     * @param array            $plugins
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function loadPlugins(array $plugins, ContainerBuilder $container,
        array $config
    ) {
        foreach ($plugins as $pluginOptions) {
            switch ($pluginOptions['type']) {
                case 'Xi\Filelib\Plugin\Image\ChangeFormatPlugin':
                    $definition = new Definition($pluginOptions['type'], array(
                        new Reference('filelib.fileoperator'),
                        $pluginOptions,
                    ));

                    break;

                case 'Xi\Filelib\Plugin\Image\VersionPlugin':
                case 'Xi\Filelib\Plugin\Video\FFmpeg\FFmpegPlugin':
                    $definition = new Definition($pluginOptions['type'], array(
                        new Reference('filelib.storage'),
                        new Reference('filelib.publisher'),
                        new Reference('filelib.fileoperator'),
                        $config['tempDir'],
                        $pluginOptions,
                    ));

                    break;

                case 'Xi\Filelib\Plugin\Video\ZencoderPlugin':
                    $definition = new Definition($pluginOptions['type'], array(
                        new Reference('filelib.storage'),
                        new Reference('filelib.publisher'),
                        new Reference('filelib.fileoperator'),
                        new Definition('Services_Zencoder', array(
                            $pluginOptions['apiKey'],
                        )),
                        new Definition('ZendService\Amazon\S3\S3', array(
                            $pluginOptions['awsKey'],
                            $pluginOptions['awsSecretKey'],
                        )),
                        $config['tempDir'],
                        $pluginOptions,
                    ));

                    break;

                default:
                    $definition = new Definition($pluginOptions['type'], array(
                        $pluginOptions,
                    ));
            }

            $definition->addTag('filelib.plugin');
            $container->setDefinition("filelib.plugins.{$pluginOptions['identifier']}", $definition);
        }
    }
}
