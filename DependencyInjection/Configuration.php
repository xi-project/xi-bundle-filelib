<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     * @todo : find out why symfony 2.1 fails on some of the following, commented methods
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filelib');

        $rootNode->children()

            ->arrayNode('renderer')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('accelerate')
                        ->defaultFalse()
                    ->end()

                    ->scalarNode('stripPrefixFromAcceleratedPath')
                        ->defaultNull()
                    ->end()

                    ->scalarNode('addPrefixToAcceleratedPath')
                        ->defaultNull()
                    ->end()

                ->end()
            ->end()

            ->scalarNode('tempDir')
                ->defaultNull()
                ->isRequired()
            ->end()
            ->scalarNode('cache')
                ->defaultNull()
            ->end()

            ->scalarNode('acl')
                ->defaultNull()
            ->end()

            ->arrayNode('queue')
                // ->defaultValue(array())
                ->children()

                    ->scalarNode('type')
                    ->end()

                    ->arrayNode('arguments')
                        ->prototype('scalar')
                        ->end()
                    ->end()

                    ->arrayNode('methods')
                        // ->useAttributeAsKey('id')
                        ->children()
                            ->scalarNode('name')
                            ->end()

                            ->arrayNode('arguments')
                            ->end()
                        ->end()
                    ->end()

                ->end()
            ->end()

            ->arrayNode('transliterator')
                // ->defaultValue(array())
                ->children()

                    ->scalarNode('type')
                    ->end()

                    ->arrayNode('arguments')
                    ->prototype('scalar')
                    ->end()
                    ->end()

                ->end()
            ->end()

            ->arrayNode('slugifier')
                // ->defaultValue(array())
                ->children()

                    ->scalarNode('type')
                    ->end()

                    ->arrayNode('arguments')
                    ->prototype('scalar')
                    ->end()
                    ->end()

                ->end()
            ->end()

            ->arrayNode('backend')
                ->isRequired()

                ->children()
                    ->scalarNode('key')
                        ->isRequired()
                    ->end()

                    ->scalarNode('method')
                        ->defaultValue('setEntityManager')
                    ->end()

                    ->scalarNode('fileEntity')
                    ->end()

                    ->scalarNode('folderEntity')
                    ->end()

                    ->scalarNode('type')
                        ->isRequired()
                    ->end()

                    ->arrayNode('options')
                        ->useAttributeAsKey('id')

                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('storage_filesystem')
                ->isRequired()

                ->children()
                    ->scalarNode('directoryPermission')
                        ->isRequired()
                    ->end()

                    ->scalarNode('filePermission')
                        ->isRequired()
                    ->end()

                    ->scalarNode('root')
                        ->isRequired()
                    ->end()

                    ->arrayNode('directoryIdCalculator')
                        ->isRequired()

                        ->children()
                            ->scalarNode('type')
                                ->isRequired()
                            ->end()

                            ->arrayNode('options')
                                ->useAttributeAsKey('id')

                                ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('publisher')
                ->isRequired()

                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                    ->end()

                    ->arrayNode('options')
                        ->useAttributeAsKey('id')

                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('profiles')
                ->prototype('array')
                    ->children()
                        ->scalarNode('identifier')
                        ->end()

                        ->scalarNode('description')
                        ->end()

                        ->scalarNode('accessToOriginal')
                        ->end()

                        ->scalarNode('publishOriginal')
                        ->end()

                        ->arrayNode('linker')
                            ->isRequired()

                            ->children()
                                ->scalarNode('type')
                                ->end()

                                ->arrayNode('options')
                                    ->useAttributeAsKey('id')

                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->variableNode('plugins')
            ->end()

        ->end();

        return $treeBuilder;
    }
}
