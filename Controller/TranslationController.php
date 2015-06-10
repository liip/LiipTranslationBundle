<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\Form\FilterType;
use Liip\TranslationBundle\Form\TranslationType;
use Liip\TranslationBundle\Model\Translation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for the translation interface
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
class TranslationController extends BaseController
{
    protected function getFilter()
    {
        $filters = $this->getFilterManager()->getCurrentFilters();

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
        foreach ($filters as $name => $value) {
            // don't display empty filter or locale which is a synonym for languages
            if (empty($value) || $name == 'locale') {
                continue;
            }

            if (is_bool($value)) {
                $value = $value ? 'filters.value.true' : 'translation.filters.value.false';
            } elseif (is_array($value)) {
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

        $filterForm = $this->createFilterForm($filters);

        return $this->render('LiipTranslationBundle:Translation:index.html.twig', array(
            'items' => $units,
            'columns' => $filters['locale'],
            'filter_form' => $filterForm->createView(),
            'filters' => $this->beautifyFilter($filters),
        ));
    }

    public function filterAction(Request $request)
    {
        $filterForm = $this->createFilterForm();
        $newFilters = $filterForm->handleRequest($request)->getData();
        $this->getFilterManager()->updateFilters($newFilters);

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function clearFilterAction()
    {
        $this->getFilterManager()->resetFilters();

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function editAction($locale, $domain, $key, Request $request)
    {
        $unit = $this->getRepository()->findByDomainAndKey($domain, $key);
        $translation = $unit->hasTranslation($locale) ? $unit->getTranslation($locale) : new Translation(null, $locale, $unit);
        $form = $this->createForm(new TranslationType(), $translation);

        if ($request->getMethod() === 'POST') {
            $translation = $form->handleRequest($request)->getData();
            if ($form->isValid()) {
                $unit->addTranslation($translation);
                $this->getRepository()->persist($unit);
                $session = $this->getSession();
                $session->getFlashBag()->set('success', 'Translation was successfully edited.');

                return $this->redirect($this->generateUrl('liip_translation_interface'));
            }
        }

        return $this->render('LiipTranslationBundle:Translation:edition.html.twig', array(
            'translation' => $translation,
            'form' => $form->createView(),
        ));
    }

    public function inlineEditAction(Request $request)
    {
        $value = $request->request->get('value');
        $id = $request->request->get('id');
        list($domain, $key, $locale) = explode('__', $id);

        $this->getRepository()->updateTranslation($locale, $domain, $key, $value);
        $session = $this->getSession();
        $session->getFlashBag()->set('success', 'Translation was successfully updated.');

        return new Response($value);
    }

    public function removeAction($locale, $domain, $key)
    {
        $this->getRepository()->removeTranslation($locale, $domain, $key);
        $session = $this->getSession();
        $session->getFlashBag()->set('success', 'Translation was successfully deleted.');

        // Cache must be cleared, so that the fallback translation get display again on the list
        $this->getTranslator()->clearCacheForLocale($locale);

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    public function exportAction(Request $request)
    {
        $response = new Response();

        $filters = $this->getFilter();
        $units = $this->getRepository()->findFiltered($filters);

        $this->getExporter()->setUnits($units);
        $zipContent = $this->getExporter()->createZipContent();

        $response->setContent($zipContent);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$this->generateZipFilename($request, $filters).'"');

        return $response;
    }

    public function cacheClearAction()
    {
        $this->getTranslator()->clearCache();
        $session = $this->getSession();
        $session->getFlashBag()->set('success', 'Cache cleared');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }

    protected function generateZipFilename(Request $request, $filters)
    {
        return 'translations-from-'.$request->getHost().'.zip';
    }

    protected function createFilterForm(array $filters = array())
    {
        return $this->createForm(new FilterType(
            $this->getAuthorizedLocale(),
            $this->getRepository()->getDomainList()
        ), $filters);
    }
}
