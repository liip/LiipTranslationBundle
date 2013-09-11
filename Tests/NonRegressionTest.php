<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NonRegressionTest extends WebTestCase
{
    public function testFallback()
    {
        $client = static::createClient();
        $client->request('GET', '/non-regression');
        $this->assertEquals(
            "  transkey1: value_1_fr_CH\n  transkey2: value_2_fr\n  transkey3: value_3_en\n  transkey4: transkey4\n",
            $client->getResponse()->getContent(),
            "Assert the fallback system: fr_CH => fr => en => raw_key"
        );
    }
}