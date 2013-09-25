<?php

namespace Liip\TranslationBundle\Security;

use Symfony\Component\Security\Core\SecurityContext;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Security
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Security {
    protected $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function isSecuredByDomain()
    {
        return $this->config['security']['by_domain'];
    }

    public function isSecuredByLocale()
    {
        return $this->config['security']['by_locale'];
    }

    public function getRoleForLocale($locale)
    {
        return 'ROLE_TRANSLATOR_LOCALE_' . strtoupper($locale);
    }

    public function getRoleForDomain($domain)
    {
        return 'ROLE_TRANSLATOR_DOMAIN_' . strtoupper($domain);
    }

    /**
     * Return the list of managed locales (defined in the bundle config)
     *
     * @return array
     */
    public function getLocaleList()
    {
        return $this->config['locale_list'];
    }

    /**
     * Return the list of locales authorized by the provided security context
     *
     * @param SecurityContext|null $securityContext
     * @return array
     */
    public function getAuthorizedLocaleList($securityContext)
    {
        if (!$securityContext || !$this->isSecuredByLocale()) {
            return $this->getLocaleList();
        }

        $authorizedLocaleList = array();
        foreach ($this->getLocaleList() as $locale) {
            if ($securityContext->isGranted($this->getRoleForLocale($locale))) {
                $authorizedLocaleList[] = $locale;
            }
        }
    }
}