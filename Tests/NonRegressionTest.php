<?php

namespace Liip\TranslationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

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
class NonRegressionTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        chdir(__DIR__.'/Fixtures/TestApplication');
        exec('./app/console cache:clear --no-warmup');
        exec('./app/console translation:import');
    }

    /**
     * Test the fallback system as defined in http://symfony.com/doc/current/book/translation.html#configuration
     */
    public function testFallback()
    {
        $client = static::createClient();
        $client->request('GET', '/non-regression/fallback');
        $this->assertEquals(
            "  fallback-key1: value_1_fr_CH\n  fallback-key2: value_2_fr\n  fallback-key3: value_3_en\n  fallback-key4: fallback-key4\n",
            $client->getResponse()->getContent(),
            "Assert the fallback system: fr_CH => fr => en => raw_key"
        );
    }

    /**
     * Test overriding as defined in http://symfony.com/doc/current/book/translation.html#translation-locations-and-naming-conventions
     */
    public function testOverriding()
    {
        $client = static::createClient();
        $client->request('GET', '/non-regression/override');

        // In SF2.0 there is only one level of overriding, so we have to adapt the expected result
        $expectedResult = Kernel::MINOR_VERSION > 0 ?
            "  override-key1: value_1_app\n  override-key2: value_2_bundle_in_app\n  override-key3: value_3_bundle\n" :
            "  override-key1: value_1_app\n  override-key2: value_2_bundle\n  override-key3: value_3_bundle\n";
        $this->assertEquals($expectedResult, $client->getResponse()->getContent(), "Assert the override system: bundle => bundle in app => app");
    }
}