<?php

namespace Liip\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{

    public function securityCheck($domain = null, $locale = null)
    {
//        if (isset($domain) && $this->get('liip.translation.manager')->isSecuredByDomain()) {
//            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
//                return new AccessDeniedHttpException("You don't have permissions to work on XXX translations");
//            }
//        }
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
     * Gets the flash messages. This is here to avoid compatibility issues between
     * Symfony 2.0 and 2.3.
     *
     * @return array
     */
    public function getFlashMessages()
    {
        $session = $this->get('session');
        if (method_exists($session, 'getFlashBag')) {
            $session->getFlashBag()->all();
        } else {
            $session->getFlashes();
        }
    }
}


//security:
//role_hierarchy:
//ROLE_TRANSLATION_ADMIN:
//- ROLE_TRANSLATION_ALL_DOMAINS
//- ROLE_TRANSLATION_ALL_LOCALES
//        ROLE_TRANSLATION_ALL_DOMAINS:
//          - ROLE_TRANSLATION_DOMAIN_*
//          ROLE_TRANSLATION_ALL_LOCALES:
//          - ROLE_TRANSLATION_LOCALE_*