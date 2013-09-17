<?php

namespace Liip\TranslationBundle\Tests\Translation\Loader;

use Liip\TranslationBundle\Translation\Loader\XliffFileLoader;

class XliffFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testMetaDataExtraction()
    {
        $loader = new XliffFileLoader();
        $catalogue = $loader->load(__DIR__.'/../../Fixtures/test.xlf', 'en', 'messages');

        $this->assertEquals(array('id'=>1), $catalogue->getMetadata('without.metadata'));
        $this->assertEquals(array('id'=>2, 'minbytes'=>'10', 'maxbytes'=>'100'), $catalogue->getMetadata('with.metadata'));
    }

    public function testNoteExtraction()
    {
        $loader = new XliffFileLoader();
        $catalogue = $loader->load(__DIR__.'/../../Fixtures/test.xlf', 'en', 'messages');

        $this->assertEquals(array('id'=>3, 'note'=>'Please edit it carefully'), $catalogue->getMetadata('with.note'));
    }

}