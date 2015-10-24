<?php

namespace Liip\TranslationBundle\Security;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Service that handle all aspect of the security.
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
class Security
{
    protected $config = array();
    protected $securityContext = null;

    public function __construct($config, $securityChecker = null)
    {
        $this->config = $config;

        $this->validateSecurityChecker($securityChecker);
        $this->securityChecker = $securityChecker;
    }

    private function validateSecurityChecker($securityChecker)
    {
        if (null === $securityChecker) {
            return;
        }

        if (!is_object($securityChecker)) {
            throw new \InvalidArgumentException(sprintf('Second parameter to Liip\TranslationBundle\Security\Security needs to be an instance of SecurityContextInterface or AuthorizationCheckerInterface, type "%s" passed', gettype($securityChecker)));
        }

        if (!$securityChecker instanceof SecurityContextInterface && !$securityChecker instanceof AuthorizationCheckerInterface) {
            throw new \InvalidArgumentException(sprintf('Second parameter to Liip\TranslationBundle\Security\Security needs to be an instance of SecurityContextInterface or AuthorizationCheckerInterface, instance of "%s" given', get_class($securityChecker)));
        }
    }

    public function isSecuredByDomain()
    {
        return !empty($this->config['security']['by_domain']);
    }

    public function isSecuredByLocale()
    {
        return !empty($this->config['security']['by_locale']);
    }

    public static function getRoleForLocale($locale)
    {
        return 'ROLE_TRANSLATOR_LOCALE_'.strtoupper($locale);
    }

    public static function getRoleForDomain($domain)
    {
        return 'ROLE_TRANSLATOR_DOMAIN_'.strtoupper($domain);
    }

    /**
     * Return the list of managed locales (defined in the bundle config).
     *
     * @return array
     */
    public function getLocaleList()
    {
        return isset($this->config['locale_list']) ? $this->config['locale_list'] : array();
    }

    /**
     * Return the list of locales authorized by the provided security context.
     *
     * @param SecurityContextInterface|AuthorizationCheckerInterface|null $securityChecker
     *
     * @return array
     */
    public function getAuthorizedLocaleList($securityChecker)
    {
        $this->validateSecurityChecker($securityChecker);

        if (!$securityChecker || !$this->isSecuredByLocale()) {
            return $this->getLocaleList();
        }

        $authorizedLocaleList = array();
        foreach ($this->getLocaleList() as $locale) {
            if ($securityChecker->isGranted(self::getRoleForLocale($locale))) {
                $authorizedLocaleList[] = $locale;
            }
        }

        return $authorizedLocaleList;
    }

    public function isGrantedForDomain($domain)
    {
        if ($this->securityChecker === null || !$this->isSecuredByDomain()) {
            return true;
        }

        return $this->securityChecker->isGranted(self::getRoleForDomain($domain));
    }

    public function isGrantedForLocale($locale)
    {
        if ($this->securityChecker === null || !$this->isSecuredByLocale()) {
            return true;
        }

        return $this->securityChecker->isGranted(self::getRoleForLocale($locale));
    }
}
