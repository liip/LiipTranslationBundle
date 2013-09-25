<?php

namespace Liip\TranslationBundle\Controller;

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
    public function indexAction($locales = null)
    {
        $context = $this->has('security.context') ? $this->get('security.context') : null;
        $baseLocales = $this->get('liip.translation.security')->getAuthorizedLocaleList($context);

        if(is_null($locales)) {
            $locales = $baseLocales;
        } else {
            $locales = explode('~', $locales);
            $locales = array_intersect($locales, $baseLocales);
        }

        return $this->render('LiipTranslationBundle:Translation:index.html.twig', array(
            'items' => $this->get('liip.translation.repository')->findAll(),
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
        $unit = $this->get('liip.translation.repository')->findByDomainAndTranslationKey($domain, $key);
        unset($unit[$locale]);
        $this->get('liip.translation.repository')->persist($unit);
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
