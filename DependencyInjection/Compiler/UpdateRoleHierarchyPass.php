<?php

namespace Liip\TranslationBundle\DependencyInjection\Compiler;

use Liip\TranslationBundle\Security\Security;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to update the security.role_hierarchy.roles
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\DependencyInjection
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class UpdateRoleHierarchyPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('security.role_hierarchy.roles')) {
            return;
        }

        $container->setParameter('security.role_hierarchy.roles', $this->updateHierarchy(
            $container->getParameter('security.role_hierarchy.roles'),
            $container->getParameter('liip_translation_config')
        ));
    }

    public function updateHierarchy($hierarchy, $config)
    {
        if ($config['security']['by_domain'] === true) {
            $hierarchy['ROLE_TRANSLATOR_ADMIN'][] = 'ROLE_TRANSLATOR_ALL_DOMAINS';
            $hierarchy['ROLE_TRANSLATOR_ALL_DOMAINS'] = $this->getDomainRoles($config);
        }

        if ($config['security']['by_locale'] === true) {
            $hierarchy['ROLE_TRANSLATOR_ADMIN'][] = 'ROLE_TRANSLATOR_ALL_LOCALES';
            $hierarchy['ROLE_TRANSLATOR_ALL_LOCALES'] = $this->getLocaleRoles($config);
        }

        return $hierarchy;
    }

    public function getDomainRoles($config)
    {
        if (count($config['security']['domain_list']) == 0) {
            throw new \Exception('To use the security by domain, you must define the security.domain_list parameter');
        }
        $roles = array();
        foreach ($config['security']['domain_list'] as $domain) {
            $roles[] = Security::getRoleForDomain($domain);
        }

        return $roles;
    }

    public function getLocaleRoles($config)
    {
        $roles = array();
        foreach ($config['locale_list'] as $locale) {
            $roles[] = Security::getRoleForLocale($locale);
        }

        return $roles;
    }
}
