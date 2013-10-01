<?php

namespace Liip\TranslationBundle\Tests\Export;
use Liip\TranslationBundle\Export\ZipExporter;
use Liip\TranslationBundle\Model\Unit;

/**
 * Add some more methods on the ZipArchive base class
 *
 * @package Liip\TranslationBundle\Tests\Export
 */
class ZipArchive extends \ZipArchive
{
    /**
     * Return the list of files
     *
     * @return array
     */
    public function getFileList() {
        $index = 0;
        $fileList = array();
        while ($index < $this->numFiles) {
            $fileList[] = $this->getNameIndex($index);
            $index++;
        }
        return $fileList;
    }
}

/**
 * Test the Zip exporter
 *
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests\Export
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class XliffFileLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testExport()
    {
        // Generate a zip file
        $exporter = new ZipExporter();
        $exporter->setUnits($this->getSomeUnits());
        $zipPath = $exporter->createZipFile();

        $zip = new ZipArchive();
        $zip->open($zipPath);

        $this->assertEquals(array(
            'message.en.yml',
            'validator.en.yml',
            'message.fr.yml',
            'validator.fr.yml',
            'message.fr_CH.yml'
        ), $zip->getFileList());

        $this->assertEquals("welcome.text: Hello\nfirst_name: 'First name'\n", $zip->getFromName('message.en.yml'));
        $this->assertEquals("welcome.text: 'Salut toi'\n", $zip->getFromName('message.fr_CH.yml'));

    }


    protected function getSomeUnits()
    {
        $unit1 = new Unit('message', 'welcome.text', array());
        $unit1->setTranslation('en', 'Hello');
        $unit1->setTranslation('fr', 'Salut');
        $unit1->setTranslation('fr_CH', 'Salut toi');

        $unit2 = new Unit('message', 'first_name', array());
        $unit2->setTranslation('en', 'First name');
        $unit2->setTranslation('fr', 'Prénom');

        $unit3 = new Unit('validator', 'not_empty', array());
        $unit3->setTranslation('en', 'Not empty error');
        $unit3->setTranslation('fr', 'Pas vide erreur');

        return array($unit1, $unit2, $unit3);
    }

}