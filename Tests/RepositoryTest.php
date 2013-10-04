<?php

namespace Liip\TranslationBundle\Tests;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Persistence\NotFoundException;
use Liip\TranslationBundle\Repository\UnitRepository;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests\Translation\Loader
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected function getLocaleList()
    {
        return array('fr', 'en');
    }

    protected function getRepository(array $units = null)
    {
        $config = array(
            'locale_list' => $this->getLocaleList()
        );

        $translator = $this->getMockBuilder('Liip\TranslationBundle\Translation\Translator');
        $translator->disableOriginalConstructor();
        $translator = $translator->getMock();

        $persistence = $this->getMock('Liip\TranslationBundle\Persistence\PersistenceInterface');
        $persistence->expects($this->any())->method('getUnits')->will($this->returnValue($units));
        $persistence->expects($this->any())->method('getUnit')->will(
            $this->returnCallback(function($domain, $key) use ($units) {
                foreach ($units as $unit) {
                    if ($unit->getDomain() == $domain && $unit->getKey() == $key) {
                        return $unit;
                    }
                }
                throw new NotFoundException($domain, $key);
            })
        );

        return new UnitRepository($config, $translator, $persistence);
    }

    public function testLocaleList()
    {
        $this->assertEquals($this->getLocaleList(), $this->getRepository()->getLocaleList());
    }

    public function testFindAll()
    {
        $this->assertEmpty($this->getRepository()->findAll());
        $this->assertCount(5, $this->getRepository(array(1, 2, 3, 4, 5))->findAll());
        // test successive loads
        $data = array('toto', 'tata', 'titi');
        $repo = $this->getRepository($data);
        $this->assertEquals($data, $repo->findAll());
        $this->assertEquals($data, $repo->findAll());
        $this->assertEquals($data, $repo->findAll());
    }

    public function testFindBy()
    {
        $u1_1 = new Unit('domain1', 'key1');
        $u1_2 = new Unit('domain1', 'key2');
        $u1_3 = new Unit('domain1', 'key3');

        $u2_1 = new Unit('domain2', 'key1');
        $u2_2 = new Unit('domain2', 'key2');

        $u3_1 = new Unit('domain3', 'key1');

        $repo = $this->getRepository(array($u1_1, $u1_2, $u1_3, $u2_1, $u2_2, $u3_1));

        $this->assertEquals(array($u1_1, $u1_2, $u1_3), $repo->findByDomain('domain1'));
        $this->assertEquals(array($u2_1, $u2_2), $repo->findByDomain('domain2'));
        $this->assertEquals(array($u3_1), $repo->findByDomain('domain3'));

        $this->assertEquals(array($u1_1, $u2_1, $u3_1), $repo->findByKey('key1'));
        $this->assertEquals(array($u1_2, $u2_2), $repo->findByKey('key2'));
        $this->assertEquals(array($u1_3), $repo->findByKey('key3'));

        $this->assertEquals($u2_2, $repo->findByDomainAndKey('domain2', 'key2'));

        $this->assertEmpty($repo->findByDomain('non-existing domain'));
        $this->assertEmpty($repo->findByKey('non-existing key'));
    }

    public function testGetDomainList()
    {
        $domains = array(
            new Unit('domain1', 'key1'),
            new Unit('domain1', 'key2'),
            new Unit('domain2', 'key1'),
            new Unit('domain2', 'key2'),
            new Unit('domain2', 'key3'),
            new Unit('domain3', 'key1'),
            new Unit('domain3', 'key2'),
        );
        $this->assertEquals(array('domain1', 'domain2', 'domain3'), $this->getRepository($domains)->getDomainList());
    }

    public function testCreateUnit()
    {
        // simply test the created unit
        $repo = $this->getRepository(array());
        $u = $repo->createUnit('domain', 'key', array());
        $this->assertTrue($u instanceof Unit);
        $this->assertEquals('domain', $u->getDomain());
        $this->assertEquals('key', $u->getKey());
        // test if the repository contains the unit (without prior load)
        $this->assertContains($u, $repo->findAll());

        // test if the repo already has a unit without prior load
        $repo = $this->getRepository(array(new Unit('d', 'k', array())));
        $u = $repo->createUnit('domain', 'key', array());
        $this->assertContains($u, $repo->findAll());

        // test without prior load empty repository
        $repo = $this->getRepository(array());
        $u = $repo->createUnit('domain', 'key', array());
        $this->assertContains($u, $repo->findAll());

        // test with prior load empty repository
        $repo = $this->getRepository(array());
        $repo->findAll();
        $u = $repo->createUnit('domain', 'key', array());
        $this->assertContains($u, $repo->findAll());

        // test without prior load but with conflict
        $repo = $this->getRepository(array(new Unit('domain', 'key', array('some data'))));
        $u = $repo->createUnit('domain', 'key', array());
        $this->assertNotContains($u, $repo->findAll());
    }

    public function testFindTranslation()
    {
        $u = new Unit('domain', 'key', array());
        $u->setTranslation('en', 'some translation');
        $u->setTranslation('fr', 'une traduction');

        $repo = $this->getRepository(array($u));
        $this->assertEquals('some translation', $repo->findTranslation('domain', 'key', 'en')->getValue());
        $this->assertEquals('une traduction', $repo->findTranslation('domain', 'key', 'fr')->getValue());
        $this->assertNull($repo->findTranslation('domain', 'key', 'non-existing locale'));
    }

    /**
     * @expectedException Liip\TranslationBundle\Persistence\NotFoundException
     * @expectedMessage "No translation unit found for domain [non-existing domain] and key [key]"
     */
    public function testFindInvalidUnitTranslation()
    {
        $repo = $this->getRepository(array());
        $repo->findTranslation('non-existing domain', 'key', 'en');
    }


}