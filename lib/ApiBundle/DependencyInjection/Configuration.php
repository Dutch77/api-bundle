<?php

namespace Asyf\ApiBundle\DependencyInjection;

use Asyf\ApiBundle\Service\Normalizer\NormalizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     *
     * todo: make this recursive
     * number of wasted hours: 1
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('asyf_api');

        $rootNode
            ->children()
                ->scalarNode('default_normalizer')
                ->end()
                ->arrayNode('entities')
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('expose')
                                ->defaultValue(true)
                            ->end()
                            ->scalarNode('normalizer')
                            ->end()
                            ->variableNode('fields')
                            ->end()
                            ->variableNode('options')
                            ->end()
                            ->variableNode('orderBy')
                                ->defaultValue([])
                            ->end()
                            ->integerNode('limit')
                                ->defaultValue(null)
                            ->end()
                            ->integerNode('offset')
                                ->defaultValue(null)
                            ->end()
                            ->arrayNode('conditions')
//                                ->defaultValue([
//                                    'field' => null,
//                                    'type' => '=',
//                                    'operator' => 'and',
//                                    'value' => null
//                                ])
//                                ->children()
//                                    ->scalarNode('field')
//                                        ->defaultValue(null)
//                                    ->end()
//                                    ->scalarNode('type')
//                                        ->defaultValue('=')
//                                    ->end()
//                                    ->scalarNode('operator')
//                                        ->defaultValue('and')
//                                    ->end()
//                                    ->scalarNode('value')
//                                        ->defaultValue(null)
//                                    ->end()
//                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
