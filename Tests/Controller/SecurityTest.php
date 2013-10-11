<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Liip\TranslationBundle\Export\ZipExporter;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\Security\Security;
use Liip\TranslationBundle\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Yaml\Yaml;

/**
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests\Controller
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class SecurityTest extends BaseWebTestCase
{
    static $configFile = '/../Fixtures/TestApplication/app/config/config.yml';

    public static function setUpBeforeClass()
    {
        // Update the config and clear the cache
        $file = __DIR__.static::$configFile;
        exec("cp $file $file.bak");
        $config = Yaml::parse(file_get_contents($file));
        $config['liip_translation']['security']['by_domain'] = true;
        $config['liip_translation']['security']['by_locale'] = true;
        file_put_contents($file,Yaml::dump($config));

        //  Import units and clear cache
        self::importUnits(array(), array('security'));
    }

    public function testRoleDefinition()
    {
        $hierarchy = $this->getContainer()->getParameter('security.role_hierarchy.roles');
        $this->assertEquals(
            array('ROLE_TRANSLATOR_ALL_DOMAINS', 'ROLE_TRANSLATOR_ALL_LOCALES'),
            $hierarchy['ROLE_TRANSLATOR_ADMIN']
        );
        $this->assertEquals(
            array('ROLE_TRANSLATOR_LOCALE_FR_CH', 'ROLE_TRANSLATOR_LOCALE_FR', 'ROLE_TRANSLATOR_LOCALE_EN'),
            $hierarchy['ROLE_TRANSLATOR_ALL_LOCALES']
        );
        $this->assertEquals(
            array('ROLE_TRANSLATOR_DOMAIN_MESSAGES', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'),
            $hierarchy['ROLE_TRANSLATOR_ALL_DOMAINS']
        );
    }

    /**
     * @dataProvider getValidAction
     */
    public function testAutorizedAction($roles, $parameters)
    {
        $this->processAction($roles, $parameters);
    }

    /**
     * @dataProvider getUnautorizedAction
     * @expectedException \Liip\TranslationBundle\Model\Exceptions\PermissionDeniedException
     */
    public function testUnautorizedAction($roles, $parameters)
    {
        $this->processAction($roles, $parameters);
    }


    public function setRoles($roles)
    {
        $roles = is_array($roles) ? $roles : array($roles);
        $this->getContainer()->get('security.context')->setToken(
            new AnonymousToken('test', 'test', $roles)
        );
    }

    public function processAction($roles, $parameters)
    {
        $this->setRoles($roles);
        $repo = $this->getContainer()->get('liip.translation.repository');
        $repo->updateTranslation($parameters['locale'], $parameters['domain'], 'key1', 'new-value');
    }

    public function getValidAction()
    {
        return array(
            array('ROLE_TRANSLATOR_ADMIN', array('domain'=>'security', 'locale'=> 'en')),
            array('ROLE_TRANSLATOR_ADMIN', array('domain'=>'security', 'locale'=> 'en')),
            array(array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_ALL_DOMAINS'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_DOMAINS', 'ROLE_TRANSLATOR_LOCALE_FR'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_DOMAINS', 'ROLE_TRANSLATOR_LOCALE_FR'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'), array('domain'=>'security', 'locale'=> 'fr'))
      );
    }

    public function getUnautorizedAction()
    {
        return array(
            array(array(), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_DOMAINS'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_LOCALES'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_DOMAIN_MESSAGES'), array('domain'=>'security', 'locale'=> 'fr')),
            array(array('ROLE_TRANSLATOR_LOCALE_EN', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'), array('domain'=>'security', 'locale'=> 'fr')),
        );
    }

    public static function tearDownAfterClass()
    {
        // put back original config
        $file = __DIR__.static::$configFile;
        exec("mv $file.bak $file");
    }
}

