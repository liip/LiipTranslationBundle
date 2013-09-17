<?php

namespace Liip\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('liip_translation')
            ->children()

                ->arrayNode('locale_list')
                    ->prototype('scalar')
                    ->cannotBeEmpty()
                    ->defaultValue(array('en', 'en_US'))
                ->end()

//            ->arrayNode('parameters')
//                ->arrayNode('persistence_layer')
//                    ->isRequired()
//                    ->prototype('array')
//                    ->children()
//                        ->scalarNode('class')
//                            ->isRequired()
//                            ->defaultValue('Liip\TranslationBundle\Model\Storage\Persistence\YamlFilePersistence')
//                        ->end()
//                        ->arrayNode('options')
//                            ->children()
//                                ->scalarNode('folder')
//                                    ->default
//                                ->end()
//                            ->end()
//                        ->end()
//                    ->end()
//                ->end()

            ->end()
        ->end();

        return $treeBuilder;
    }
}
