<?php

namespace Liip\TranslationBundle\Persistence;

use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Persistence\Propel\UnitQuery;
use Liip\TranslationBundle\Persistence\Propel\Unit as PropelUnit;
use Symfony\Component\Locale\Exception\NotImplementedException;
use Symfony\Component\Yaml\Yaml;

/**
 * Persistence layer based on Propel
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

class PropelPersistence implements PersistenceInterface {


    /**
     * Retrieve all persisted units from the persistence layer..
     *
     * @return Unit[]
     */
    public function getUnits()
    {
        $propelUnits = UnitQuery::create()->leftJoinTranslation()->find();
        $units = array();
        foreach($propelUnits as $propelUnit){
            $units[] = $propelUnit->convertToModel();
        }

        return $units;
    }

    /**
     * Save the given Units to the persistence layer.
     *
     * @param Unit[] $units
     * @return bool
     */
    public function saveUnits(array $units)
    {
        foreach($units as $unit) {
            $this->saveUnit($unit);
        }
    }

    /**
     * Save the given Unit to the persistence layer.
     *
     * @param Unit $unit
     * @return bool
     */
    public function saveUnit(Unit $unit)
    {
        $propelUnit = UnitQuery::create()->findOneByDomainAndKey($unit->getDomain(), $unit->getTranslationKey());
        if (!$propelUnit) {
            $propelUnit = new PropelUnit();
        }
        $propelUnit->updateFromModel($unit);
        $propelUnit->save();
    }
}