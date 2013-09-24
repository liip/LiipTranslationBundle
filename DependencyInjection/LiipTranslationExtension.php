<?php

namespace Liip\TranslationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
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
class LiipTranslationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('liip_translation_config', $config);
        $container->setParameter('liip.translation.persistance.class', $config['persistence']['class']);
        $container->setParameter('liip.translation.persistance.options', $config['persistence']['options']);


        $this->defineSecurityRoles($config, $container);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Dynamically define roles based on locales and domains
     *
     * @param array $config                 The bundle raw config
     * @param ContainerBuilder $container   The main container
     */
    public function defineSecurityRoles(array $config, ContainerBuilder $container)
    {
        $hierarchy = $container->getParameter('security.role_hierarchy.roles');

        if ($config['security']['by_locale'] === true) {
            foreach ($config['locale_list'] as $locale) {
                $hierarchy['ROLE_TRANSLATOR_ALL_LOCALES'][] = 'ROLE_TRANSLATOR_LOCALE_'.strtoupper($locale);
            }
        }

        if ($config['security']['by_domain'] === true) {
            $domains = array(); // TODO retrieve the domain list from somewhere...
            foreach ($domains as $domain) {
                $hierarchy['ROLE_TRANSLATOR_ALL_DOMAINS'][] = 'ROLE_TRANSLATOR_DOMAIN_'.strtoupper($domain);
            }
        }

        $container->setParameter('security.role_hierarchy.roles', $hierarchy);
    }

}
