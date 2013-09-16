<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $this->assertEquals(
            "  override-key1: value_1_app\n  override-key2: value_2_bundle_in_app\n  override-key3: value_3_bundle\n",
            $client->getResponse()->getContent(),
            "Assert the override system: bundle => bundle in app => app"
        );
    }
}