<?php

namespace Liip\TranslationBundle\Persistence;

use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Model\Translation;

/**
 * Define a common interface for persisting translation units
 *
 *  WARNING THIS INTERFACE IS NOT ORTHOGONAL, Getter will return unit and associated translations
 *  but saveXXX and deleteXXX are going to work only unit or translation
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
interface PersistenceInterface
{
    public function getUnit($domain, $key);
    public function getUnits();

    public function saveUnit(Unit $unit);
    public function saveUnits(array $units);

    public function deleteUnit(Unit $unit);
    public function deleteUnits(array $units);

    public function saveTranslation(Translation $translation);
    public function saveTranslations(array $translations);

    public function deleteTranslation(Translation $translation);
    public function deleteTranslations(array $translations);

}
