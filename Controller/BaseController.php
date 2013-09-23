<?php

namespace Liip\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BaseController extends Controller
{

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
        if (isset($domain) && $this->get('liip.translation.manager')->isSecuredByDomain()) {
            $domainRole = $this->get('liip.translation.manager')->getRoleForDomain($domain);
            if (!$this->get('security.context')->isGranted('ROLE_TRANSLATION_ALL_DOMAINS') || !$this->get('security.context')->isGranted($domainRole)
            ) {
                throw new AccessDeniedHttpException("You don't have permissions to work on translations for domain [$domain]");
            }
        }

        if (isset($locale) && $this->get('liip.translation.manager')->isSecuredByLocale()) {
            $localeRole = $this->get('liip.translation.manager')->getRoleForLocale($locale);
            if (!$this->get('security.context')->isGranted('ROLE_TRANSLATION_ALL_LOCALES') || !$this->get('security.context')->isGranted($localeRole)
            ) {
                throw new AccessDeniedHttpException("You don't have permissions to work on translations for locale [$locale]");
            }
        }
    }

}

