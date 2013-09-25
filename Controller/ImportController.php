<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\Form\FileImportType;
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
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class ImportController extends BaseController
{
    public function indexAction()
    {
        return $this->render('LiipTranslationBundle:Import:index.html.twig', array(
            'form' => $this->createForm(new FileImportType())->createView(),
            'translations' => $this->getImporter()->getCurrentTranslations(),
        ));
    }

    public function uploadAction()
    {
        $this->securityCheck();

        $form = $this->createForm(new FileImportType());
        $data = $this->handleForm($form);

        try {
            $this->getImporter()->handleUploadedFile($data['file']);
            $this->addFlashMessage('success', 'File import success');
        }
        catch (\Exception $e) {
            $this->addFlashMessage('error', 'Error while trying to import: '.$e->getMessage());
        }

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    public function removeEntryAction($locale, $domain, $key)
    {
        $this->securityCheck($domain, $locale);

        $this->getImporter()->remove($domain, $key, $locale);
        $this->addFlashMessage('success', 'Entry removed');

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    public function processAction($locale)
    {
        $this->securityCheck(null, $locale);

        $this->getImporter()->persists($this->getPersistence(), $locale);
        $this->addFlashMessage('success', 'Import success');

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

}
