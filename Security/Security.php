<?php

namespace Liip\TranslationBundle\Security;

use Symfony\Component\Security\Core\SecurityContext;

/**
 * Service that handle all aspect of the security
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
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Security
{
    protected $config = array();
    protected $securityContext = null;

    public function __construct($config, SecurityContext $securityContext = null)
    {
        $this->config = $config;
        $this->securityContext = $securityContext;
    }

    public function isSecuredByDomain()
    {
        return $this->config['security']['by_domain'];
    }

    public function isSecuredByLocale()
    {
        return $this->config['security']['by_locale'];
    }

    public static function getRoleForLocale($locale)
    {
        return 'ROLE_TRANSLATOR_LOCALE_' . strtoupper($locale);
    }

    public static function getRoleForDomain($domain)
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
     *
     * @return array
     */
    public function getAuthorizedLocaleList($securityContext)
    {
        if (!$securityContext || !$this->isSecuredByLocale()) {
            return $this->getLocaleList();
        }

        $authorizedLocaleList = array();
        foreach ($this->getLocaleList() as $locale) {
            if ($securityContext->isGranted(self::getRoleForLocale($locale))) {
                $authorizedLocaleList[] = $locale;
            }
        }

        return $authorizedLocaleList;
    }

    public function isGrantedForDomain($domain)
    {
        if ($this->securityContext === null || !$this->isSecuredByDomain()) {
            return true;
        }

        return $this->securityContext->isGranted(self::getRoleForDomain($domain));
    }

    public function isGrantedForLocale($locale)
    {
        if ($this->securityContext === null || !$this->isSecuredByLocale()) {
            return true;
        }

        return $this->securityContext->isGranted(self::getRoleForLocale($locale));
    }
}
