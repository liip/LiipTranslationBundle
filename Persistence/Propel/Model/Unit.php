<?php

namespace Liip\TranslationBundle\Persistence\Propel\Model;

use Liip\TranslationBundle\Persistence\Propel\Model\om\BaseUnit;
use Liip\TranslationBundle\Persistence\Propel\Model\Translation as PropelTranslation;
use Liip\TranslationBundle\Model\Unit as ModelUnit;

/**
 * The propel representation of a unit.
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Unit extends BaseUnit
{
    /**
     * Create a ModelUnit representation of a this object.
     *
     * @return ModelUnit
     */
    public function convertToModel()
    {
        $unit = new \Liip\TranslationBundle\Model\Unit(
            $this->getDomain(),
            $this->getKey(),
            $this->getMetadata()
        );
        foreach ($this->getTranslations() as $translation) {
            $unit->setTranslation($translation->getLocale(), $translation->getValue());
        }

        return $unit;
    }

    /**
     * Update the current object from a ModelUnit.
     *
     * @param ModelUnit $unit
     */
    public function updateFromModel(ModelUnit $unit)
    {
        $this->setDomain($unit->getDomain());
        $this->setKey($unit->getKey());
        $this->setMetadata($unit->getMetadata());
    }

    /**
     * Get or create a Propel Tranlation object for the given locale.
     *
     * @param string $locale
     *
     * @return Translation
     */
    public function getOrCreateTranslationForLocale($locale)
    {
        foreach ($this->getTranslations() as $propelTranslation) {
            if ($propelTranslation->getLocale() == $locale) {
                return $propelTranslation;
            }
        }

        $propelTranslation = new PropelTranslation();
        $propelTranslation->setLocale($locale);
        $this->addTranslation($propelTranslation);

        return $propelTranslation;
    }
}
