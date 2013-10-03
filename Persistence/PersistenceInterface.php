<?php

namespace Liip\TranslationBundle\Persistence;

use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Model\Translation;

/**
 * Define a common interface for persisting translation units
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
    /**
     * Retrieve a single unit from the persistence layer.
     *
     * @param string $domain
     * @param string $key
     * @return Unit
     * @throw NotFoundException
     */
    public function getUnit($domain, $key);

    /**
     * Retrieve all persisted units from the persistence layer.
     *
     * @return Unit[]
     */
    public function getUnits();

    /**
     * Save the given Units to the persistence layer.
     *
     * @param Unit[] $units
     * @return bool
     */
    public function saveUnits(array $units);

    /**
     * Save the given Unit to the persistence layer.
     *
     * @param Unit $unit
     * @return bool
     */
    public function saveUnit(Unit $unit);

    public function deleteTranslations(array $translations);
    public function deleteTranslation(Translation $translation);
    public function saveTranslations(array $translations);
    public function saveTranslation(Translation $translation);
}
