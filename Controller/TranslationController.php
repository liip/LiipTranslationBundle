<?php

namespace Liip\TranslationBundle\Controller;

class TranslationController extends BaseController
{
    public function indexAction($locales = null)
    {
        $authorizedLocales = $this->get('liip.translation.manager')->getLocaleList();
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

    public function cacheClearAction()
    {
        $this->get('liip.translation.manager')->clearSymfonyCache();
        $this->addFlashMessage('success', 'Cache cleared');

        return $this->redirect($this->generateUrl('liip_translation_interface'));
    }
}
