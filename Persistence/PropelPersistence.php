<?php

namespace Liip\TranslationBundle\Persistence;

use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Persistence\Propel\Formatter\UnitFormatter;
use Liip\TranslationBundle\Persistence\Propel\Model\UnitQuery;
use Liip\TranslationBundle\Persistence\Propel\Model\Unit as PropelUnit;
use Liip\TranslationBundle\Persistence\Propel\Model\TranslationQuery;
use Liip\TranslationBundle\Persistence\Propel\Model\Translation as PropelTranslation;

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
class PropelPersistence implements PersistenceInterface
{

    /**
     * @inheritdoc
     */
    public function getUnit($domain, $key)
    {
        $unit = UnitQuery::create()->leftJoinWith('Translation')
            ->filterByDomain($domain)->filterByKey($key)
            ->setFormatter(new UnitFormatter())
            ->findOne();

        if ($unit == null) {
            throw new NotFoundException($domain, $key);
        }

        return $unit;
    }

    /**
     * Retrieve all persisted units from the persistence layer..
     *
     * @return Unit[]
     */
    public function getUnits()
    {
        return UnitQuery::create()->leftJoinWith('Translation')
            ->setFormatter(new UnitFormatter())
            ->find();
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

    /**
     * Save the given Units to the persistence layer.
     *
     * @param Unit[] $units
     * @return bool
     */
    public function saveUnits(array $units)
    {
        // TODO save all at once
        foreach($units as $unit) {
            $this->saveUnit($unit);
        }
    }

    public function deleteUnit(Unit $unit)
    {
        UnitQuery::create()->findOneByDomainAndKey(
            $unit->getDomain(), $unit->getTranslationKey()
        )->delete();
    }

    public function deleteUnits(array $units)
    {
        foreach($units as $unit) {
            $this->deleteUnit($unit);
        }
    }


    public function deleteTranslation(Translation $translation)
    {
        $propelUnit = UnitQuery::create()->findOneByDomainAndKey(
            $translation->getUnit()->getDomain(),
            $translation->getUnit()->getTranslationKey()
        );

        $propelTranslation = TranslationQuery::create()
            ->filterByUnitId($propelUnit->getId())
            ->filterByLocale($translation->getLocale())
            ->findOne()
            ->delete()
        ;
    }


    public function deleteTranslations(array $translations)
    {
        // TODO use a single query
        foreach($translations as $translation) {
            $this->deleteTranslation($translation);
        }
    }


    public function saveTranslations(array $translations)
    {
        foreach($translations as $translation) {
            $this->saveTranslation($translation);
        }
    }

    public function saveTranslation(Translation $translation)
    {
        // Fetch the related unit
        $propelUnit = UnitQuery::create()->findOneByDomainAndKey(
            $translation->getUnit()->getDomain(),
            $translation->getUnit()->getTranslationKey()
        );

        // Retrived or create the translation
        $propelTranslationToUpdate = null;
        foreach($propelUnit->getTranslations() as $propelTranslation) {
            if ($propelTranslation->getLocale() == $translation->getLocale()) {
                $propelTranslationToUpdate = $propelTranslation;
                break;
            }
        }
        if ($propelTranslationToUpdate === null){
            $propelTranslationToUpdate = new PropelTranslation();
            $propelTranslationToUpdate->setUnitId($propelUnit->getId());
        }

        // Update and save
        $propelTranslationToUpdate->updateFromModel($translation);
        $propelTranslationToUpdate->save();
    }
}
