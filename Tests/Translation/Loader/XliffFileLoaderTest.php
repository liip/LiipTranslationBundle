<?php

namespace Liip\TranslationBundle\Tests\Translation\Loader;

use Liip\TranslationBundle\Translation\Loader\XliffFileLoader;

/**
 * Test the custom XLIFF Loader.
 *
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
class XliffFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testMetaDataExtraction()
    {
        $loader = new XliffFileLoader();
        $catalogue = $loader->load(__DIR__.'/../../Fixtures/test.xliff', 'en', 'messages');

        $this->assertEquals(array('id' => 1), $catalogue->getMetadata('without.metadata'));
        $this->assertEquals(array('id' => 2, 'minbytes' => '10', 'maxbytes' => '100'), $catalogue->getMetadata('with.metadata'));
    }

    public function testNoteExtraction()
    {
        $loader = new XliffFileLoader();
        $catalogue = $loader->load(__DIR__.'/../../Fixtures/test.xliff', 'en', 'messages');

        $this->assertEquals(array('id' => 3, 'note' => 'Please edit it carefully'), $catalogue->getMetadata('with.note'));
    }
}
