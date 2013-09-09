<?php

namespace Liip\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TranslationController extends Controller
{
    public function indexAction()
    {
        return $this->render('LiipTranslationBundle:Default:index.html.twig', array('translations' =>
            array('key1' => 'translation1', 'key2' => 'translation2')
        ));
    }
}
