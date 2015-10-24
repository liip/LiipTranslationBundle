<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Liip\TranslationBundle\Export\ZipExporter;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;

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
class ImportControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        self::importUnits();
    }

    public function setup()
    {
        $repo = $this->getContainer()->get('liip.translation.repository');
        $repo->updateTranslation('en', 'functional', 'key1', 'value1');
        $repo->updateTranslation('en', 'functional', 'key2', 'value2');
        $repo->updateTranslation('en', 'functional', 'compound.translation1', 'compound');
    }

    public function testEmptyPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/import');
        $this->assertEquals(0, $crawler->filter('table')->count());
        $this->assertContains('Please upload a new file ...', $client->getResponse()->getContent());
    }

    public function testUploadYml()
    {
        // Upload it
        $client = static::createClient();
        $crawler = $client->request('GET', '/import');
        $form = $crawler->filter('input[value="Upload"]')->form();
        $client->submit($form, array('translation_file_import[file]' => $this->createYml()));
        $crawler = $client->followRedirect();
        $this->assertNotContains('alert-error', $client->getResponse()->getContent());

        // Check the results
        $this->assertEquals('File import success, 1 new and 2 update', $crawler->filter('.alert-info')->text());
        $this->assertNotContains('key1', $client->getResponse()->getContent());
        $this->assertContains('key2', $crawler->filter('table.updated-translations')->text());
        $this->assertContains('compound.translation1', $crawler->filter('table.updated-translations')->text());
        $this->assertContains('key3', $crawler->filter('table.new-translations')->text());
    }

    public function testUploadZip()
    {
        // Upload the zip
        $client = static::createClient();
        $crawler = $client->request('GET', '/import');
        $form = $crawler->filter('input[value="Upload"]')->form();
        $client->submit($form, array('translation_file_import[file]' => $this->createZip()));
        $crawler = $client->followRedirect();
        $this->assertNotContains('alert-error', $client->getResponse()->getContent());

        // Check the results
        $this->assertEquals('File import success, 4 new and 2 update', $crawler->filter('.alert-info')->text());
        $this->assertContains('compound 2 en', $crawler->filter('table.new-translations')->eq(0)->text());
        $this->assertContains('value_fr', $crawler->filter('table.new-translations')->eq(1)->text());
        $this->assertContains('compound 2 fr', $crawler->filter('table.new-translations')->eq(1)->text());
        $this->assertContains('new_value2', $crawler->filter('table.updated-translations')->text());
    }

    public function testRemoval()
    {
        // Upload the zip
        $client = static::createClient();
        $crawler = $client->request('GET', '/import');
        $form = $crawler->filter('input[value="Upload"]')->form();
        $client->submit($form, array('translation_file_import[file]' => $this->createZip()));
        $crawler = $client->followRedirect();
        $this->assertNotContains('alert-error', $client->getResponse()->getContent());

        // Check the results
        $this->assertEquals('File import success, 4 new and 2 update', $crawler->filter('.alert-info')->text());

        // Remove the updated entry
        $this->assertContains('new_value2', $client->getResponse()->getContent());
        $crawler = $client->click($crawler->filter('a[href="/import/remove-entry/en/functional/key2"]')->eq(0)->link());
        $crawler = $client->followRedirect();
        $this->assertNotContains('new_value2', $client->getResponse()->getContent());

        // Remove one of the new entry
        $this->assertContains('value2_fr', $client->getResponse()->getContent());
        $crawler = $client->click($crawler->filter('a[href="/import/remove-entry/fr/functional/key2"]')->eq(0)->link());
        $crawler = $client->followRedirect();
        $this->assertNotContains('value2_fr', $client->getResponse()->getContent());
    }

    public function testProcessing()
    {
        // Upload the zip
        $client = static::createClient();
        $crawler = $client->request('GET', '/import');
        $form = $crawler->filter('input[value="Upload"]')->form();
        $client->submit($form, array('translation_file_import[file]' => $this->createZip()));
        $crawler = $client->followRedirect();
        $this->assertEquals('File import success, 4 new and 2 update', $crawler->filter('.alert-info')->text());

        // Process the import
        $form = $crawler->filter('input[value="Import everything"]')->form();
        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check results
        $this->assertEquals($crawler->filter('.alert-success')->text(), 'Import success (4 created, 2 modified and 0 removed)');

        // Test to use one of the new key
        $this->assertEquals('new_value2', $this->getContainer()->get('translator')->trans('key2', array(), 'functional', 'en'));
    }

    protected function createYml()
    {
        $path = sys_get_temp_dir().'/functional.en.yml';
        file_put_contents($path, Yaml::dump(array(
            'compound.translation1' => 'new compound value',
            'key1' => 'value1',
            'key2' => 'import-value2',
            'key3' => 'import-value3',
        )));

        return new UploadedFile($path, 'functional.en.yml');
    }

    protected function createZip()
    {
        // Then we create the zip
        $unit1 = new Unit('functional', 'key1');
        $unit1->setTranslation('en', 'value1');
        $unit1->setTranslation('fr', 'value_fr');
        $unit2 = new Unit('functional', 'key2');
        $unit2->setTranslation('en', 'new_value2');
        $unit2->setTranslation('fr', 'value2_fr');
        $unit3 = new Unit('functional', 'compound.translation2');
        $unit3->setTranslation('en', 'compound 2 en');
        $unit3->setTranslation('fr', 'compound 2 fr');
        $unit4 = new Unit('functional', 'compound.translation1');
        $unit4->setTranslation('en', 'new compound 2 en');
        $exporter = new ZipExporter();
        $exporter->setUnits(array($unit1, $unit2, $unit3, $unit4));

        return new UploadedFile($exporter->createZipFile(sys_get_temp_dir().'/trans.zip'), 'trans.zip');
    }
}
