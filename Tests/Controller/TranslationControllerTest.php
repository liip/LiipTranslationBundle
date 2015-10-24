<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Liip\TranslationBundle\Tests\BaseWebTestCase;
use Symfony\Component\HttpKernel\Kernel;

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
class TranslationControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        self::importUnits();
    }

    public function testFiltering()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $this->getUrl('liip_translation_interface'));
        $form = $crawler->filter('input[type="submit"][value="Filter"]')->form();

        // Filter by languagues must reduces the number of columns
        $formData = array();
        if (Kernel::MINOR_VERSION == 0) {
            $formData['translation_filter[languages][fr_CH]'] = 'fr_CH';
        } else {
            $formData['translation_filter[languages]'] = array('fr_CH');
        }
        $client->submit($form, $formData);
        $crawler = $client->followRedirect();
        $this->assertEquals(3, $crawler->filter('table.translations thead th')->count());
    }

    public function testOverriding()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $this->getUrl('liip_translation_interface'));

        // Find a trans item
        $transKey = $crawler->filter('#functional__key1__en');
        $this->assertEquals('value1', $transKey->text());

        // Find and click the edit link
        $editLink = $transKey->parents()->eq(0)->filter('a.translation-override')->eq(0)->link();
        $crawler = $client->click($editLink);

        // Edit the value
        $form = $crawler->filter('input[type="submit"]')->form();
        $client->submit($form, array(
            'translation_translation[value]' => 'new_value1',
        ));
        $crawler = $client->followRedirect();

        // Check that the change is effective
        $transKey = $crawler->filter('#functional__key1__en');
        $this->assertEquals('new_value1', $transKey->text());
    }

    public function testInlineEditing()
    {
        // Generate the ajax call
        $client = static::createClient();
        $client->request('POST', $this->getUrl('liip_translation_inline_edit'), array(
                'value' => 'new_value2_for_fr',
                'id' => 'functional__key2__fr',
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        // Check the rsult on the list
        $crawler = $client->request('GET', $this->getUrl('liip_translation_interface'));
        $transKey = $crawler->filter('#functional__key2__fr');
        $this->assertEquals('new_value2_for_fr', $transKey->text());
    }

    /**
     * @depends testOverriding
     */
    public function testRemoving()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $this->getUrl('liip_translation_interface'));

        // Find the remove link and click it
        $transKey = $crawler->filter('#functional__key1__en');
        $editLink = $transKey->parents()->eq(0)->filter('a.translation-remove')->eq(0)->link();
        $client->click($editLink);
        $crawler = $client->followRedirect();

        // Check that the change is effective
        $transKey = $crawler->filter('#functional__key1__en');
        $this->assertEquals('value1', $transKey->text());
    }

    public function testExport()
    {
        // First we edit some translations for export
        $repo = $this->getContainer()->get('liip.translation.repository');
        $repo->updateTranslation('en', 'functional', 'export-key1', 'override-val-for-key1');
        $repo->updateTranslation('en', 'functional', 'export-key2', 'override-val-for-key2');
        $repo->updateTranslation('fr', 'functional', 'export-key1', 'override-val-for-key1_in_fr');

        // Execute the export
        $client = static::createClient();
        $crawler = $client->request('GET', $this->getUrl('liip_translation_interface'));
        $crawler = $client->click($crawler->filter('a.translation-export')->eq(0)->link());

        // Basic checks
        $this->assertEquals('application/zip', $client->getResponse()->headers->get('Content-Type'));
        $this->assertContains('functional.en.yml', $client->getResponse()->getContent());
        $this->assertContains('functional.fr.yml', $client->getResponse()->getContent());
    }

    public function testCacheClear()
    {
        // First we change the label of the cache clear button
        $client = static::createClient();
        $client->request('POST', $this->getUrl('liip_translation_inline_edit'), array(
                'value' => 'Clear cache now!',
                'id' => 'translation-bundle__button.clear_cache__en',
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        // We check the name on the interface, and see it's still the old one
        $crawler = $client->request('GET', $this->getUrl('liip_translation_interface'));
        $link = $crawler->filter('a.translation-cache-clear');
        $this->assertEquals('Clear cache', $link->text());

        // Now we clear the cache and check the button again
        $client->click($link->link());
        $crawler = $client->followRedirect();
        $link = $crawler->filter('a.translation-cache-clear');
        $this->assertEquals('Clear cache now!', $link->text());
    }
}
