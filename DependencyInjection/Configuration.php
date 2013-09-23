<?php

namespace Liip\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\DependencyInjection
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('liip_translation', 'array')
            ->children()

                ->arrayNode('locale_list')
                    ->defaultValue(array('en', 'en_US'))
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('persistence')
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->defaultValue('Liip\TranslationBundle\Model\Storage\Persistence\YamlFilePersistence')
                        ->end()
                        ->arrayNode('options')
                            ->children()
                                ->scalarNode('folder')
                                    ->defaultValue("%kernel.root_dir%/data/translations")
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('security')
                    ->children()
                        ->booleanNode('by_domain')
                            ->defaultValue(false)
                        ->end()
                        ->booleanNode('by_locale')
                            ->defaultValue(false)
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
