<?php

namespace Liip\TranslationBundle\Tests\Controller;

use Liip\TranslationBundle\Tests\BaseWebTestCase;

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
class ImportControllerTest extends BaseWebTestCase
{
    public function testIndex()
    {
        $content = $this->fetchContent($this->getUrl('liip_translation_import'));
    }
}
