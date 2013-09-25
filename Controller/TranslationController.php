<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\DependencyInjection\Configuration;
use Liip\TranslationBundle\Form\TranslationType;
use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;

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
class TranslationController extends BaseController
{
    public function indexAction()
    {
        $context = $this->has('security.context') ? $this->get('security.context') : null;
        $baseLocales = $this->get('liip.translation.security')->getAuthorizedLocaleList($context);

        $filters = $this->get('session')->get(Configuration::SESSION_PREFIX.'filters', array());

        if(! isset($filters['locale']) || is_null($filters['locale']) || empty($filters['locale'])) {
            $locales = $baseLocales;
        } else {
            if(! is_array($filters['locale'])) {
                $filters['locale'] = array($filters['locale']);
            }
            $locales = array_intersect($filters['locale'], $baseLocales);
        }

        /** @var Unit[] $units */
        if(!isset($filters['domain']) || is_null($filters['domain'])) {
            $units = $this->get('liip.translation.repository')->findAll();
        } else {
            $units = $this->get('liip.translation.repository')->findByDomain($filters['domain']);
        }

        foreach($units as $k => $u) {
            $count = 0;
            foreach($locales as $l) {
                if(! isset($u[$l]) || strlen(trim($u[$l]->getValue())) == 0) {
                    ++$count;
                }
            }
            if(isset($filters['empty']) && $count == 0) {
                unset($units[$k]);
            }
        }


        return $this->render('LiipTranslationBundle:Translation:index.html.twig', array(
            'items' => $units,
            'columns' => $locales
        ));
    }

    public function editAction($locale, $domain, $key)
    {
        $translation = $this->get('liip.translation.repository')->findTranslation($domain, $key, $locale);

        $form = $this->createForm(new TranslationType(), $translation, array());
        if ($this->getRequest()->getMethod() === 'POST') {
            /** @var Translation $data */
            $translation = $this->handleForm($form);
            if($form->isValid()) {
                $this->get('liip.translation.repository')->persist($translation->getUnit());
                $this->addFlashMessage('success', 'Translation was successfully edited.');
                return $this->redirect($this->generateUrl('liip_translation_interface'));
            }
        }

        return $this->render('LiipTranslationBundle:Translation:edition.html.twig', array(
            'translation' => $translation,
            'form' => $form->createView()
        ));
    }

    public function removeAction($locale, $domain, $key)
    {
        $this->get('liip.translation.repository')->removeTranslation($locale, $domain, $key);
        $this->addFlashMessage('success', 'Translation was successfully deleted.');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function cacheClearAction()
    {
        $this->get('liip.translation.repository')->clearSymfonyCache();
        $this->addFlashMessage('success', 'Cache cleared');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }
}
