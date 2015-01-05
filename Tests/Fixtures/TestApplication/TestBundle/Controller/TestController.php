<?php

namespace Liip\TranslationBundle\Tests\Fixtures\TestApplication\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Return some basic pages used in the non regression tests
 *
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class TestController extends Controller
{
    public function fallbackTestAction(Request $request)
    {
        $request->setLocale('fr_CH'); // symfony <= 2.5
        $this->get('translator')->setLocale('fr_CH'); // symfony >= 2.6

        return $this->render('TestBundle:Test:keys_values.html.twig', array(
            'keys' => array('fallback-key1', 'fallback-key2', 'fallback-key3', 'fallback-key4'),
            'domain' => 'fallback-test',
        ));
    }

    public function overrideTestAction(Request $request)
    {
        $request->setLocale('en');

        return $this->render('TestBundle:Test:keys_values.html.twig', array(
            'keys' => array('override-key1', 'override-key2', 'override-key3'),
            'domain' => 'override-test',
        ));
    }

    public function noImportTestAction(Request $request)
    {
        $request->setLocale('en');

        return $this->render('TestBundle:Test:keys_values.html.twig', array(
            'keys' => array('no-import-key1', 'no-import-key2'),
            'domain' => 'no-import-test',
        ));
    }
}
