<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\DependencyInjection\Configuration;
use Liip\TranslationBundle\Form\FilterType;
use Liip\TranslationBundle\Form\TranslationType;
use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Symfony\Component\HttpFoundation\Response;

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
    protected function getFilterForm(array $filters = array())
    {
        return $this->createForm(new FilterType(
            $this->getAuthorizedLocale(),
            $this->get('liip.translation.repository')->getDomainList()
        ), $filters);
    }

    protected function getAuthorizedLocale()
    {
        $context = $this->has('security.context') ? $this->get('security.context') : null;
        return $this->get('liip.translation.security')->getAuthorizedLocaleList($context);
    }

    public function indexAction()
    {
        $baseLocales = $this->getAuthorizedLocale();
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
        if(!isset($filters['domain']) || is_null($filters['domain']) || empty($filters['domain'])) {
            $units = $this->get('liip.translation.repository')->findAll();
        } else {
            $units = $this->get('liip.translation.repository')->findByDomain($filters['domain']);
        }

        foreach($units as $k => $u) {
            $filterEmpty = isset($filters['empty']) && $filters['empty'];
            $filterKey = isset($filters['key']) && strlen(trim($filters['key'])) > 0 ? trim($filters['key']) : null;
            $filterValue = isset($filters['value']) && strlen(trim($filters['value'])) > 0 ? trim($filters['value']) : null;

            if($filterKey && strpos($u->getTranslationKey(), $filterKey) === false) {
                unset($units[$k]);
                continue;
            }

            $count = 0;
            $valueCount = 0;
            foreach($locales as $l) {
                $value = isset($u[$l]) ? trim($u[$l]->getValue()) : null;
                if(! isset($u[$l]) || strlen($value) == 0) {
                    ++$count;
                }

                if($filterValue && strpos($value, $filterValue) !== false) {
                    ++$valueCount;
                }
            }
            if(($filterEmpty && $count == 0) || ($filterValue && $valueCount == 0)) {
                unset($units[$k]);
            }
        }

        $filterForm = $this->getFilterForm($filters);
        return $this->render('LiipTranslationBundle:Translation:index.html.twig', array(
            'items' => $units,
            'columns' => $locales,
            'filter_form' => $filterForm->createView()
        ));
    }

    public function filterAction()
    {
        $filterForm = $this->getFilterForm();
        $filters = $this->handleForm($filterForm);
        $this->get('session')->set(Configuration::SESSION_PREFIX.'filters', $filters);

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function editAction($locale, $domain, $key)
    {
        $unit = $this->get('liip.translation.repository')->findByDomainAndTranslationKey($domain, $key);
        if(isset($unit[$locale])) {
            $translation = $unit[$locale];
        } else {
            $unit->setTranslation($locale, '');
            $translation = $unit->getTranslation($locale);
        }

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

    public function exportAction()
    {
        $response = new Response();

        $exporter = $this->get('liip.translation.exporter');
        $exporter->setUnits($this->get('liip.translation.repository')->findAll());
        $zipContent = $exporter->createZipContent();

        $response->setContent($zipContent);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="vca-translations.zip"');

        return $response;
    }

    public function cacheClearAction()
    {
        $this->get('liip.translation.repository')->clearSymfonyCache();
        $this->addFlashMessage('success', 'Cache cleared');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }
}
