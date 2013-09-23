<?php

namespace Liip\TranslationBundle\Tests\Fixtures\TestApplication\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
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