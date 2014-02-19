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
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('xi_filelib');

        $rootNode->children()

            ->arrayNode('authorization')
                ->children()
                    ->scalarNode('enabled')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('adapter_service')
                        ->defaultNull()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('storage')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('file_permission')
                        ->defaultValue('600')
                    ->end()
                    ->scalarNode('directory_permission')
                        ->defaultValue('700')
                    ->end()
                    ->scalarNode('root')
                        ->defaultValue('%kernel.root_dir%/data/files')
                    ->end()
                    ->variableNode('directory_id_calculator')
                        ->defaultNull()
                    ->end()
            ->end()
            ->end()

            ->variableNode('queue')
                ->defaultNull()
            ->end()

            ->arrayNode('renderer')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enable_acceleration')
                        ->defaultFalse()
                    ->end()

                    ->scalarNode('strip_prefix')
                        ->defaultValue('')
                    ->end()

                    ->scalarNode('add_prefix')
                        ->defaultValue('')
                    ->end()

                ->end()
            ->end()

            ->scalarNode('temp_dir')
                ->defaultNull()
            ->end()

            ->variableNode('plugins')
            ->end()

            ->arrayNode('profiles')
                ->prototype('scalar')
                ->end()
            ->end()

            ->arrayNode('publisher')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('automatic_publisher')
                        ->defaultFalse()
                    ->end()
                            ->booleanNode('beautifurls')
                        ->defaultFalse()
                    ->end()
                    ->variableNode('adapter')
                        ->defaultNull()
                    ->end()
            ->end()
            ->end()

            ->arrayNode('twig')
                ->children()
                    ->scalarNode('not_found_url')
                    ->defaultValue('//place.manatee.lc/14/300/300.svg')
                    ->end()
                ->end()
            ->end()


        ->end();

        return $treeBuilder;
    }
}
