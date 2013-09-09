<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TranslationControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/translations');
        $this->assertTrue($crawler->filter('h1:contains("Translation interface")')->count() > 0);
    }
}
