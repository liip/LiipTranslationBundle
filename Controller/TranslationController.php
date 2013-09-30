<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\DependencyInjection\Configuration;
use Liip\TranslationBundle\Form\FilterType;
use Liip\TranslationBundle\Form\TranslationType;
use Liip\TranslationBundle\Model\Translation;
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
            $this->getRepository()->getDomainList()
        ), $filters);
    }

    protected function getFilter()
    {
        $filters = $this->getSession()->get(Configuration::SESSION_PREFIX . 'filters', array());

        $authorizedLocales = $this->getAuthorizedLocale();
        // we use two different keys so that the display of a full / empty selection doesn't affect the list
        // of authorized locales that must be filtered anyway.
        if (!isset($filters['languages']) || is_null($filters['languages']) || empty($filters['languages'])) {
            $filters['locale'] = $authorizedLocales;
        } else {
            if (!is_array($filters['languages'])) {
                $filters['languages'] = array($filters['languages']);
            }
            $filters['locale'] = array_intersect($filters['languages'], $authorizedLocales);
        }
        return $filters;
    }

    protected function beautifyFilter($filters)
    {
        $result = array();
        foreach($filters as $name => $value) {
            // don't display empty filter or locale which is a synonym for languages
            if(empty($value) || $name == 'locale') {
                continue;
            }

            if(is_bool($value)) {
                $value = $value ? 'filters.value.true' : 'translation.filters.value.false';
            } else if(is_array($value)) {
                $value = implode(', ', $value);
            }
            $result['filters.'.$name] = $value;
        }
        return $result;
    }

    public function indexAction()
    {
        $filters = $this->getFilter();
        $units = $this->getRepository()->findFiltered($filters);

        $filterForm = $this->getFilterForm($filters);
        return $this->render('LiipTranslationBundle:Translation:index.html.twig', array(
            'items' => $units,
            'columns' => $filters['locale'],
            'filter_form' => $filterForm->createView(),
            'filters' => $this->beautifyFilter($filters)
        ));
    }

    public function filterAction()
    {
        $filterForm = $this->getFilterForm();
        $filters = $this->handleForm($filterForm);
        $this->getSession()->set(Configuration::SESSION_PREFIX.'filters', $filters);

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function clearFilterAction()
    {
        $this->getSession()->set(Configuration::SESSION_PREFIX.'filters', null);

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function editAction($locale, $domain, $key)
    {
        $unit = $this->getRepository()->findByDomainAndTranslationKey($domain, $key);
        if(! $unit->hasTranslation($locale)) {
            $unit->setTranslation($locale, '');
        }
        $translation = $unit->getTranslation($locale);

        $form = $this->createForm(new TranslationType(), $translation, array());
        if ($this->getRequest()->getMethod() === 'POST') {
            /** @var Translation $data */
            $translation = $this->handleForm($form);
            if($form->isValid()) {
                $this->getRepository()->persist($translation->getUnit());
                $this->addFlashMessage('success', 'Translation was successfully edited.');
                return $this->redirect($this->generateUrl('liip_translation_interface'));
            }
        }

        return $this->render('LiipTranslationBundle:Translation:edition.html.twig', array(
            'translation' => $translation,
            'form' => $form->createView()
        ));
    }

    public function inlineEditAction()
    {
        $value = $this->getRequest()->request->get('value');
        $id = $this->getRequest()->request->get('id');
        list($domain, $key, $locale) = explode('__', $id);

        $unit = $this->getRepository()->findByDomainAndTranslationKey($domain, $key);
        $unit->setTranslation($locale, $value);
        $this->getRepository()->persist();

        exit($value);
    }

    public function removeAction($locale, $domain, $key)
    {
        $this->getRepository()->removeTranslation($locale, $domain, $key);
        $this->addFlashMessage('success', 'Translation was successfully deleted.');

        // Cache must be cleared, so that the fallback translation get display again on the list
        $this->getTranslator()->clearCacheForLocale($locale);

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function exportAction()
    {
        $response = new Response();

        $filters = $this->getFilter();
        $units = $this->getRepository()->findFiltered($filters);

        $this->getExporter()->setUnits($units);
        $zipContent = $this->getExporter()->createZipContent();

        $response->setContent($zipContent);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="vca-translations.zip"');

        return $response;
    }

    public function cacheClearAction()
    {
        $this->getSymfonyImporter()->clearSymfonyCache();
        $this->addFlashMessage('success', 'Cache cleared');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }
}
