<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\Form\FileImportType;
use Liip\TranslationBundle\Import\ImportException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to handle file importation. When a file is imported, its content
 * is first placed into the session so it can be validated or eventually modified.
 * The user can then import it definitively into the managed translations.
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
class ImportController extends BaseController
{
    /**
     * Importating dashboard
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('LiipTranslationBundle:Import:index.html.twig', array(
            'upload_form' => $this->createForm(new FileImportType())->createView(),
            'translations' => $this->getSessionImporter()->getCurrentTranslations(),
        ));
    }

    /**
     * Clear the current dashboard
     *
     * @return Response
     */
    public function resetAction()
    {
        $this->getSessionImporter()->clear();

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    /**
     * File upload handling, redirect to the dashboard
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadAction()
    {
        $form = $this->createForm(new FileImportType());
        $data = $this->handleForm($form);

        try {
            $counters = $this->getSessionImporter()->handleUploadedFile($data['file']);
            if ($counters['new'] == 0 && $counters['updated'] == 0) {
                $this->addFlashMessage('warning', 'File import success, but not more modification found');
            } else {
                $this->addFlashMessage('info', "File import success, {$counters['new']} new and {$counters['updated']} update");
            }
        } catch (ImportException $e) {
            $this->addFlashMessage('error', 'Error while trying to import: '.$e->getMessage());
        }

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    /**
     * Remove an entry from the session
     *
     * @param string $locale
     * @param string $domain
     * @param string $key
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeEntryAction($locale, $domain, $key)
    {
        $this->securityCheck($domain, $locale);

        $this->getSessionImporter()->remove($domain, $key, $locale);
        $this->addFlashMessage('success', 'Entry removed');

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    /**
     * Process the importation for the given locale, and redirect to the dashboard
     *
     * @param string $locale
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function processAction($locale)
    {
        $stats = $this->getSessionImporter()->comfirmImportation($locale);
        $this->addFlashMessage('success', "Import success ({$stats['translations']['text']})");

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }
}
