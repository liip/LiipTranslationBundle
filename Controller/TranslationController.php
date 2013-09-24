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
        $authorizedLocales = $this->get('liip.translation.manager')->getAuthorizedLocaleList(
            $this->get('security.context')
        );
        if(is_null($locales)) {
            $locales = $authorizedLocales;
        } else {
            $locales = explode('~', $locales);
            $locales = array_intersect($locales, $authorizedLocales);
        }

        return $this->render('LiipTranslationBundle:Translation:index.html.twig', array(
            'items' => $this->get('liip.translation.storage')->getAllTranslationUnits(),
            'columns' => $locales
        ));
    }

    public function editAction($locale, $domain, $key)
    {
        // FIXME getTranslation should return the Translation directly
        $translation = $this->get('liip.translation.storage')->getTranslation($locale, $domain, $key);

        $unit = new Unit($domain, $key, array());
        $translation = new Translation($translation, $locale, $unit);

        $form = $this->createForm(new TranslationType(), $translation, array());
        if ($this->getRequest()->getMethod() === 'POST') {
            /** @var Translation $data */
            $data = $this->handleForm($form);
            if($form->isValid()) {
                // FIXME storage should know how to manage Translation directly
                $this->get('liip.translation.storage')->updateTranslation($data->getLocale(), $data->getDomain(), $data->getKey(), $data->getValue());
                $this->get('liip.translation.storage')->save();

                $this->addFlashMessage('success', 'Translation was successfully edited.');
                return $this->redirect($this->generateUrl('liip_translation_interface'));
            }
        }

        return $this->render('LiipTranslationBundle:Translation:edition.html.twig', array(
            'translation' => $translation,
            'form' => $form->createView()
        ));
    }

    public function cacheClearAction()
    {
        $this->get('liip.translation.manager')->clearSymfonyCache();
        $this->addFlashMessage('success', 'Cache cleared');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }
}
