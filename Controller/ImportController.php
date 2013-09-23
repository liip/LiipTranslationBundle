<?php

namespace Liip\TranslationBundle\Controller;

use Liip\TranslationBundle\Form\FileImportType;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends BaseController
{
    public function indexAction()
    {
        $this->securityCheck();

        return $this->render('LiipTranslationBundle:Import:index.html.twig', array(
            'form' => $this->createForm(new FileImportType())->createView(),
            'translations' => $this->get('liip.translation.importer')->getCurrentTranslations(),
        ));
    }

    public function uploadAction()
    {
        $this->securityCheck();

        $form = $this->createForm(new FileImportType());
        $data = $form->handleRequest($this->getRequest())->getData();

        try {
            $this->get('liip.translation.importer')->handleUploadedFile($data['file']);
            $this->get('session')->getFlashBag()->set('success', 'File import success');
        }
        catch (\Exception $e) {
            $this->get('session')->getFlashBag()->set('error', 'Error while trying to import thr file: '.$e->getMessage());
        }

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    public function removeEntryAction($locale, $domain, $key)
    {
        $this->securityCheck();

        $this->get('liip.translation.importer')->removeEntry($locale, $domain, $key);
        $this->get('session')->getFlashBag()->set('success', 'Entry removed');

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

    public function processAction($locale)
    {
        $this->securityCheck();

        $this->get('liip.translation.importer')->processImport($locale);
        $this->get('session')->getFlashBag()->set('success', 'Import success');

        return $this->redirect($this->generateUrl('liip_translation_import'));
    }

}
