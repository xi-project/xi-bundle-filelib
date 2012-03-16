<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
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
                
            ->arrayNode('backend')
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
                        ->children()
                            ->scalarNode('type')
                            ->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
                
            ->arrayNode('publisher')
                
                ->children()
                
                    ->scalarNode('type')
                    ->end()

                    ->arrayNode('options')
                        ->useAttributeAsKey('id')
                        ->prototype('scalar')->end()
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
                
                    ->arrayNode('linker')
                
                        ->children()
                
                            ->scalarNode('type')
                            ->end()
                
                            ->scalarNode('accessToOriginal')
                            ->end()
                
                            ->scalarNode('publishOriginal')
                            ->end()
                
                            ->arrayNode('options')
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')->end()
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
