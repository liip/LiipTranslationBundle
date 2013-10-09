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
        $config['liip_translation']['security']['by_domain'] = false;
        $config['liip_translation']['security']['by_locale'] = true;
        file_put_contents($file,Yaml::dump($config));
        static::clearCache();
    }

    public function setUp()
    {
        /** @var UnitRepository $repo */
        $repo = $this->getContainer()->get('liip.translation.repository');
        $this->setRoles('ROLE_TRANSLATION_ADMIN');
        $repo->createUnit('security', 'key1');
        $repo->persist();
    }

    /**
     * @dataProvider getValidAction
     */
    public function testAutorizedAction($action, $roles, $parameters)
    {
        $this->processAction($action, $roles, $parameters);
    }

    /**
     * @dataProvider getUnautorizedAction
     * @expectedException \Liip\TranslationBundle\Model\Exceptions\PermissionDeniedException
     */
    public function testUnautorizedAction($action, $roles, $parameters)
    {
        $this->markTestSkipped("Currently not working when running all the test suite, maybe we should do kind of container refresh");
        $this->processAction($action, $roles, $parameters);
    }


    public function setRoles($roles)
    {
        $roles = is_array($roles) ? $roles : array($roles);
        $this->getContainer()->get('security.context')->setToken(
            new AnonymousToken('test', 'test', $roles)
        );
    }

    public function processAction($action, $roles, $parameters)
    {
        // Update the user roles
        $this->setRoles($roles);

        // process an edit or a removal
        $repo = $this->getContainer()->get('liip.translation.repository');
        switch ($action) {
            case 'update':
                $repo->updateTranslation($parameters['locale'], $parameters['domain'], 'key1', 'new-value');
                break;
            case 'remove':
                $repo->removeTranslation($parameters['locale'], $parameters['domain'], 'key1');
                break;
            default:
                throw new \Exception("Invalid action [$action]");
        }
    }

    public function getValidAction()
    {
        return array(
            array('update', 'ROLE_TRANSLATOR_ADMIN', array('domain'=>'security', 'locale'=> 'en')),
            array('remove', 'ROLE_TRANSLATOR_ADMIN', array('domain'=>'security', 'locale'=> 'en')),

            array('update', array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_ALL_DOMAINS'), array('domain'=>'security', 'locale'=> 'fr')),

            array('update', array('ROLE_TRANSLATOR_ALL_DOMAINS', 'ROLE_TRANSLATOR_LOCALE_FR'), array('domain'=>'security', 'locale'=> 'fr')),
            array('remove', array('ROLE_TRANSLATOR_ALL_DOMAINS', 'ROLE_TRANSLATOR_LOCALE_FR'), array('domain'=>'security', 'locale'=> 'fr')),

            array('update', array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'), array('domain'=>'security', 'locale'=> 'fr')),
            array('remove', array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'), array('domain'=>'security', 'locale'=> 'fr'))
      );
    }

    public function getUnautorizedAction()
    {
        return array(
            array('update', array(), array('domain'=>'security', 'locale'=> 'fr')),
            array('update', array('ROLE_TRANSLATOR_ALL_DOMAINS'), array('domain'=>'security', 'locale'=> 'fr')),
//            array('update', array('ROLE_TRANSLATOR_ALL_LOCALES'), array('domain'=>'security', 'locale'=> 'fr')),
//            array('update', array('ROLE_TRANSLATOR_ALL_LOCALES', 'ROLE_TRANSLATOR_DOMAIN_MESSAGES'), array('domain'=>'security', 'locale'=> 'fr')),
            array('update', array('ROLE_TRANSLATOR_LOCALE_EN', 'ROLE_TRANSLATOR_DOMAIN_SECURITY'), array('domain'=>'security', 'locale'=> 'fr')),
        );
    }

    public static function tearDownAfterClass()
    {
        // put back original config
        $file = __DIR__.static::$configFile;
        exec("mv $file.bak $file");
    }
}

