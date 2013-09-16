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
                ->scalarNode('locale_list')
                    ->cannotBeEmpty()
                    ->defaultValue(array('en', 'fr', 'fr_CH'))
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
