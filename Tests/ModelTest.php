<?php

namespace Liip\TranslationBundle\Tests;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;

/**
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests\Translation\Loader
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    const DOMAIN = 'test-domain';
    const KEY = 'test.key';
    const TRANSLATION1 = 'this is a test translation';
    const LOCALE1 = 'en';
    const TRANSLATION2 = 'ceci est une traduction de test';
    const LOCALE2 = 'fr';

    public function getUnit()
    {
        return array(
            array(new Unit(self::DOMAIN, self::KEY, array())),
        );
    }

    public function getUnitWithTranslation()
    {
        $u = new Unit(self::DOMAIN, self::KEY, array());
        $u->setTranslation(self::LOCALE1, self::TRANSLATION1);
        $u->setTranslation(self::LOCALE2, self::TRANSLATION2);

        return array(array($u));
    }

    public function testUnitCreation()
    {
        $metadata = array();
        $u = new Unit(self::DOMAIN, self::KEY, $metadata);
        $this->assertEquals(self::DOMAIN, $u->getDomain());
        $this->assertEquals(self::KEY, $u->getKey());
        $this->assertEquals($metadata, $u->getMetadata());
        $this->assertEquals('-', $u->getHelp());

        $metadata = array('some_metadata' => 'fancy data', 'another' => 'more fancy data', 'note' => 'You can use a placeholder %price%');
        $u = new Unit(self::DOMAIN, self::KEY, $metadata);
        $this->assertEquals($metadata, $u->getMetadata());
        $this->assertEquals('You can use a placeholder %price%', $u->getHelp());
    }

    /**
     * @dataProvider getUnit
     */
    public function testTranslationCreation(Unit $unit)
    {
        $t = new Translation(self::TRANSLATION1, self::LOCALE1, $unit);
        $this->assertEquals(self::TRANSLATION1, $t->getValue());
        $this->assertEquals(self::LOCALE1, $t->getLocale());
        $this->assertEquals($unit, $t->getUnit());
        $this->assertEquals(self::DOMAIN, $t->getDomain());
        $this->assertEquals(self::KEY, $t->getKey());
    }

    /**
     * @dataProvider getUnit
     */
    public function testSetTranslation(Unit $unit)
    {
        $this->assertFalse($unit->hasTranslation(self::LOCALE1));

        $unit->setTranslation(self::LOCALE1, self::TRANSLATION1);
        $this->assertTrue($unit->hasTranslation(self::LOCALE1));
        $this->assertFalse($unit->hasTranslation(self::LOCALE2));

        $unit->setTranslation(self::LOCALE2, self::TRANSLATION2);
        $this->assertTrue($unit->hasTranslation(self::LOCALE1));
        $this->assertTrue($unit->hasTranslation(self::LOCALE2));

        $this->assertTrue($unit->getTranslation(self::LOCALE2) instanceof Translation);
        $this->assertEquals(self::TRANSLATION1, $unit->getTranslation(self::LOCALE1)->getValue());
        $this->assertEquals(self::TRANSLATION2, $unit->getTranslation(self::LOCALE2)->getValue());
        $this->assertNull($unit->getTranslation('non-existing locale'));

        $translations = $unit->getTranslations();
        $this->assertCount(2, $translations);
        $this->assertEquals(self::TRANSLATION1, $translations[self::LOCALE1]->getValue());
        $this->assertEquals(self::TRANSLATION2, $translations[self::LOCALE2]->getValue());

        $unit->setTranslation(self::LOCALE1, self::TRANSLATION2);
        $this->assertEquals(self::TRANSLATION2, $unit->getTranslation(self::LOCALE1)->getValue());
    }

    /**
     * @dataProvider getUnit
     */
    public function testAddTranslation(Unit $unit)
    {
        $t = new Translation(self::TRANSLATION1, self::LOCALE1, $unit);
        $unit->addTranslation($t);
        $this->assertEquals(self::TRANSLATION1, $unit->getTranslation(self::LOCALE1)->getValue());
    }

    /**
     * @dataProvider getUnitWithTranslation
     */
    public function testArrayAccess(Unit $unit)
    {
        $this->assertTrue(isset($unit[self::LOCALE1]));
        $this->assertFalse(isset($unit['non-existing locale']));

        $this->assertEquals(self::TRANSLATION2, $unit[self::LOCALE2]);
        $this->assertFalse($unit[self::LOCALE2] instanceof Translation);
        $this->assertTrue(is_string($unit[self::LOCALE2]));

        $this->assertFalse($unit['non-existing locale']);

        $unit[self::LOCALE2] = self::TRANSLATION1;
        $this->assertEquals(self::TRANSLATION1, $unit[self::LOCALE2]);

        $count = 0;
        foreach ($unit->getTranslations() as $t) {
            $this->assertContains($t->getLocale(), array(self::LOCALE1, self::LOCALE2));
            $this->assertTrue($t instanceof Translation);
            ++$count;
        }
        $this->assertEquals(2, $count);

        unset($unit[self::LOCALE1]);
        $this->assertFalse(isset($unit[self::LOCALE1]));
    }

    /**
     * @dataProvider getUnit
     * @expectedException \RuntimeException
     */
    public function testOffsetSetWithoutLocale(Unit $unit)
    {
        $unit[] = new Translation('test', 'test', $unit);
    }

    /**
     * @dataProvider getUnit
     */
    public function testEmptyTranslation(Unit $unit)
    {
        $unit->setTranslation(self::LOCALE1, '');
        $this->assertTrue($unit->hasTranslation(self::LOCALE1));
        $this->assertTrue(isset($unit[self::LOCALE1]));
        $this->assertEquals('', $unit->getTranslation(self::LOCALE1));
        $count = 0;
        foreach ($unit->getTranslations() as $t) {
            ++$count;
        }
        $this->assertEquals(1, $count);
    }
}
