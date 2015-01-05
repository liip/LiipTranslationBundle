<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\Export\ZipExporter;
use Liip\TranslationBundle\Filter\FilterManager;
use Liip\TranslationBundle\Import\SessionImporter;
use Liip\TranslationBundle\Import\SymfonyImporter;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\Security\Security;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Base class with some helper methods for the controller of the bundle.
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Controller
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
abstract class BaseController extends Controller
{
    /**
     * Handle a form submission. This is here to avoid compatibility issues
     * between Symfony 2.0 and 2.1.
     *
     * @param Form $form the form that must be handled
     *
     * @return mixed data as returned by the getData() method
     */
    public function handleForm(Form $form)
    {
        if (method_exists($form, 'handleRequest')) {
            $data = $form->handleRequest($this->getRequest())->getData();
        } else {
            $form->bindRequest($this->getRequest());
            $data = $form->getData();
        }

        return $data;
    }

    /**
     * Sets a flash message. This is here to avoid compatibility issues between
     * Symfony 2.0 and 2.3.
     *
     * @param string $type    the type of the flash message
     * @param string $message the message itself
     */
    public function addFlashMessage($type, $message)
    {
        $session = $this->getSession();

        if (method_exists($session, 'getFlashBag')) {
            $session->getFlashBag()->set($type, $message);
        } else {
            $session->setFlash($type, $message);
        }
    }

    /**
     * Process security for the provided locale or domain. Either one of the both
     * parameters can be null if we don't need to check them.
     *
     * @param string $domain domain to check for
     * @param string $locale locale to check for
     *
     * @throws AccessDeniedHttpException
     */
    public function securityCheck($domain = null, $locale = null)
    {
        if (isset($domain) && $this->getSecurity()->isSecuredByDomain()) {
            if (!$this->getSecurityContext()->isGranted(Security::getRoleForDomain($domain))) {
                throw new AccessDeniedHttpException("You don't have permissions to work on translations for domain [$domain]");
            }
        }

        if (isset($locale) && $this->getSecurity()->isSecuredByLocale()) {
            if (!$this->getSecurityContext()->isGranted(Security::getRoleForLocale($locale))) {
                throw new AccessDeniedHttpException("You don't have permissions to work on translations for locale [$locale]");
            }
        }
    }

    protected function getAuthorizedLocale()
    {
        $context = $this->getSecurityContext();

        return $this->getSecurity()->getAuthorizedLocaleList($context);
    }

    /**
     * @return SessionImporter
     */
    protected function getSessionImporter()
    {
        return $this->get('liip.translation.session_importer');
    }

    /**
     * @return SymfonyImporter
     */
    protected function getSymfonyImporter()
    {
        return $this->get('liip.translation.symfony_importer');
    }

    /**
     * @return PersistenceInterface
     */
    protected function getPersistence()
    {
        return $this->get('liip.translation.persistence');
    }

    /**
     * @return UnitRepository
     */
    protected function getRepository()
    {
        return $this->get('liip.translation.repository');
    }

    /**
     * @return ZipExporter
     */
    protected function getExporter()
    {
        return $this->get('liip.translation.exporter');
    }

    /**
     * @return Security
     */
    protected function getSecurity()
    {
        return $this->get('liip.translation.security');
    }

    /**
     * @return FilterManager
     */
    protected function getFilterManager()
    {
        return $this->get('liip.translation.filter');
    }

    /**
     * @return SecurityContext|null
     */
    protected function getSecurityContext()
    {
        return $this->has('security.context') ? $this->get('security.context') : null;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }
}
