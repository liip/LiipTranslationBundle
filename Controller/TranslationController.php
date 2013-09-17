<?php

namespace Liip\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TranslationController extends Controller
{
    public function indexAction()
    {
        return $this->render('LiipTranslationBundle:Default:index.html.twig', array(
            'items' => $this->get('liip.translation.storage')->getAllTranslationUnits(),
            'columns' => $this->get('liip.translation.manager')->getLocaleList()
        ));
    }
}
