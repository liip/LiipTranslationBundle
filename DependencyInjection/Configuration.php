<?php

namespace Liip\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Configuration implements ConfigurationInterface
{
    const SESSION_PREFIX = 'liip.translation.';

    /**
     * {@inheritdoc}
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
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return !isset($v['service']) && !isset($v['class']);
                        })
                        ->then(function ($v) {
                            $v['class'] = 'Liip\TranslationBundle\Persistence\YamlFilePersistence';

                            return $v;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return $v['service'] !== null && $v['class'] !== null;
                        })
                        ->thenInvalid('You cannot set both the service and class in the persistence configuration')
                    ->end()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('class')->defaultNull()->end()
                        ->arrayNode('options')
                            ->defaultValue(array('folder' => '%kernel.root_dir%/data/translations'))
                            ->prototype('variable')->end()
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
                        ->arrayNode('domain_list')
                            ->defaultValue(array('en', 'en_US'))
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('interface')
                    ->children()
                        ->variableNode('default_filters')
                            ->defaultValue(array())
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
