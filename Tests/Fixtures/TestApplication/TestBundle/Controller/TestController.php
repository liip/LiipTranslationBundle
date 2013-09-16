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
        return $this->render('TestBundle:Test:keys_values.html.twig', array(
            'keys' => array('fallback-key1', 'fallback-key2', 'fallback-key3', 'fallback-key4'),
            'domain' => 'fallback-test'
        ));
    }

    public function overrideTestAction()
    {
        $this->getRequest()->setLocale('en');
        return $this->render('TestBundle:Test:keys_values.html.twig', array(
            'keys' => array('override-key1', 'override-key2', 'override-key3'),
            'domain' => 'override-test'
        ));
    }
}