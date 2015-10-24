<?php

namespace Liip\TranslationBundle\Tests;

/**
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class NonRegressionTest extends BaseWebTestCase
{
    /**
     * Test the fallback system as defined in http://symfony.com/doc/current/book/translation.html#configuration.
     */
    public function testFallback()
    {
        self::importUnits();

        $client = static::createClient();
        $client->request('GET', '/non-regression/fallback');
        $this->assertEquals(
            "  fallback-key1: value_1_fr_CH\n  fallback-key2: value_2_fr\n  fallback-key3: value_3_en\n  fallback-key4: fallback-key4\n",
            substr($client->getResponse()->getContent(), 0, 1000),
            'Assert the fallback system: fr_CH => fr => en => raw_key'
        );
    }

    /**
     * Test overriding as defined in http://symfony.com/doc/current/book/translation.html#translation-locations-and-naming-conventions.
     */
    public function testOverriding()
    {
        self::importUnits();

        $client = static::createClient();
        $client->request('GET', '/non-regression/override');

        $expectedResult = "  override-key1: value_1_app\n  override-key2: value_2_bundle_in_app\n  override-key3: value_3_bundle\n";
        $this->assertEquals(
            $expectedResult,
            substr($client->getResponse()->getContent(), 0, 1000),
            'Assert the override system: bundle => bundle in app => app'
        );
    }

    /**
     * Tests translations without running the translation:import command.
     */
    public function testNoImport()
    {
        self::clearCache();

        $client = static::createClient();
        $client->request('GET', '/non-regression/no-import');
        $this->assertEquals(
            "  no-import-key1: value_1_app\n  no-import-key2: no-import-key2\n",
            substr($client->getResponse()->getContent(), 0, 1000),
            'Assert that translations work even when the storage is empty'
        );
    }
}
