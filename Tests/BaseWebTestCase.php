<?php

namespace Liip\TranslationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class BaseWebTestCase extends WebTestCase {
    public function assertStatusCode($code, Client $client, $message = null)
    {
        $status = $client->getResponse()->getStatusCode();
        if(is_array($code)) {
            if(is_null($message)) {
                $message = 'The status code [%s] is not in the expected ones : [%s].';
            }
            $this->assertContains($status, $code, sprintf($message, $status, implode(', ', $code)));
        } else {
            if (is_null($message)) {
                $message = 'The status code [%s] does not match expected [%s].';
            }
            $this->assertEquals($code, $status, sprintf($message, $status, $code));
        }
    }

    public function assertRedirect(Client $client)
    {
        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function assertNoError(Client $client)
    {
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function getUrl($path, array $parameters = array())
    {
        return $this->getContainer()->get('router')->generate($path, $parameters);
    }
}