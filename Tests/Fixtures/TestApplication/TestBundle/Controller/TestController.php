<?php

namespace Liip\TranslationBundle\Tests\Fixtures\TestApplication\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function fallbackTestAction()
    {
        $this->getRequest()->setLocale('fr_CH');
        return $this->render('TestBundle:Test:fallback.html.twig', array(
            'keys' => array('transkey1', 'transkey2', 'transkey3', 'transkey4')
        ));
    }
}