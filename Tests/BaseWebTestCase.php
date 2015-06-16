<?php

namespace Liip\TranslationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class BaseWebTestCase extends WebTestCase
{
    /**
     * Executes the given SF2 command.
     */
    protected static function executeCommand($cmd)
    {
        exec(realpath(__DIR__.'/Fixtures/TestApplication').'/'.$cmd);
    }

    /**
     * Runs the cache:clear command.
     */
    protected static function clearCache()
    {
        self::executeCommand('app/console cache:clear --no-warmup');
    }

    /**
     * Clears the cache and runs the translation:import command.
     */
    public static function importUnits($locales = array(), $domains = array())
    {
        self::clearCache();
        $localesParam = empty($locales) ? '' : '--locales='.implode(',', $locales);
        $domainsParam = empty($domains) ? '' : '--domains='.implode(',', $domains);
        self::executeCommand("app/console liip:translation:import $localesParam $domainsParam -vvv");
    }

    public function assertStatusCode($code, Client $client, $message = null)
    {
        $status = $client->getResponse()->getStatusCode();
        if (is_array($code)) {
            if (is_null($message)) {
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
}
