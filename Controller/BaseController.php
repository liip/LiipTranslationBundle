<?php

namespace Liip\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Controller
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class BaseController extends Controller
{
    /**
     * Handle a form submission. This is here to avoid compatibility issues
     * between Symfony 2.0 and 2.1.
     *
     * @param Form $form
     * @return mixed
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
     * @param $type string
     * @param $message string
     */
    public function addFlashMessage($type, $message)
    {
        $session = $this->get('session');
        if(method_exists($session, 'getFlashBag')) {
            $session->getFlashBag()->set($type, $message);
        } else {
            $session->setFlash($type, $message);
        }
    }

    /**
     * Process security for the provided locale or domain
     *
     * @param string $domain
     * @param string $locale
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function securityCheck($domain = null, $locale = null)
    {
        if (isset($domain) && $this->get('liip.translation.security')->isSecuredByDomain()) {
            $domainRole = $this->get('liip.translation.security')->getRoleForDomain($domain);
            if (!$this->get('security.context')->isGranted('ROLE_TRANSLATOR_ALL_DOMAINS') || !$this->get('security.context')->isGranted($domainRole)
            ) {
                throw new AccessDeniedHttpException("You don't have permissions to work on translations for domain [$domain]");
            }
        }

        if (isset($locale) && $this->get('liip.translation.security')->isSecuredByLocale()) {
            $localeRole = $this->get('liip.translation.security')->getRoleForLocale($locale);
            if (!$this->get('security.context')->isGranted('ROLE_TRANSLATOR_ALL_LOCALES') || !$this->get('security.context')->isGranted($localeRole)
            ) {
                throw new AccessDeniedHttpException("You don't have permissions to work on translations for locale [$locale]");
            }
        }
    }

}

