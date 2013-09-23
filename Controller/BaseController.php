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