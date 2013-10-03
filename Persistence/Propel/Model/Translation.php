<?php

namespace Liip\TranslationBundle\Persistence\Propel\Model;

use Liip\TranslationBundle\Persistence\Propel\Model\om\BaseTranslation;
use Liip\TranslationBundle\Model\Translation as ModelTranslation;


/**
 * A propel representation of a translation
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Persistence
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Translation extends BaseTranslation
{

    /**
     * Update this object from a ModelTranslation
     *
     * @param ModelTranslation $unit
     */
    public function updateFromModel(ModelTranslation $translation)
    {
        $this->setLocale($translation->getLocale());
        $this->setValue($translation->getValue());
        $this->setMetadata($translation->getMetadata());
    }

}
