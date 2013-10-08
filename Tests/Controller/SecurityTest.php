<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Liip\TranslationBundle\Export\ZipExporter;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests\Controller
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
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
        static::clearCache();
    }

    public function testCheckByDomain()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testCheckByLocale()
    {
        $this->markTestIncomplete('TODO');
    }

    public static function tearDownAfterClass()
    {
        // put back original config
        $file = __DIR__.static::$configFile;
        exec("mv $file.bak $file");
    }
}

