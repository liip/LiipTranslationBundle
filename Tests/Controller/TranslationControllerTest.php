<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TranslationControllerTest extends WebTestCase
{
    public function testIndex()
    {
//        $this->markTestIncomplete(
//            'This test has not been implemented yet.'
//        );

        $client = static::createClient();
        $crawler = $client->request('GET', '/translations');
//        $this->assertCount(1, $crawler->filter('h1:contains("Translation interface")')->count());
    }
}
