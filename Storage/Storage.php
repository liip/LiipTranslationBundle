<?php

namespace Liip\TranslationBundle\Storage;

use Liip\TranslationBundle\Storage\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Model\Unit;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Storage
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Storage {

    /** @var  PersistenceInterface */
    protected $persistence;
    protected $loaded = false;
    /** @var Unit[] $units */
    protected $unitsPerDomainAndKey = array();
    /** @var string[] */
    protected $allDomains = array();


    public function __construct(PersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * Load the data from the persistence layer
     */
    protected function load()
    {
        if ($this->loaded) {
            return;
        }
        $units = $this->persistence->getUnits();
        foreach($units as $unit) {
            $this->unitsPerDomainAndKey[$unit->getDomain()][$unit->getTranslationKey()] = $unit;
            $this->allDomains[$unit->getDomain()] = true;
        }
        $this->allDomains = array_keys($this->allDomains);

        $this->loaded = true;
    }

    /**
     * Save the current unit and translation into the Persistence
     */
    public function save() {
        $this->persistence->saveUnits($this->getAllTranslationUnits());
    }

    /**
     * Create or update a translation unit
     * @param $domain
     * @param $key
     * @param $metadata
     */
    public function createOrUpdateTranslationUnit($domain, $key, array $metadata = array())
    {
        $this->load();
        if(isset($this->unitsPerDomainAndKey[$domain][$key])) {
            $this->unitsPerDomainAndKey[$domain][$key]->setMetadata($metadata);
        } else {
            $this->unitsPerDomainAndKey[$domain][$key] = new Unit($domain, $key, $metadata);
        }
    }

    /**
     * Set a base translation value
     * @param $locale
     * @param $domain
     * @param $key
     * @param $value
     */
    public function setBaseTranslation($locale, $domain, $key , $value)
    {
        $this->load();
        if (isset($this->unitsPerDomainAndKey[$domain][$key])) {
            $this->unitsPerDomainAndKey[$domain][$key]->setTranslation($locale, $value);
        }
    }

    public function getTranslations()
    {
        $this->load();
        $translations = array();
        foreach($this->unitsPerDomainAndKey as $domain => $keys) {
            foreach($keys as $key => $unit) {
                foreach($unit->getTranslations() as $t) {
                    $translations[$domain][$key][$t->getLocale()] = $t->getValue();
                }
            }
        }
        return $translations;
    }

    public function getAllDomains()
    {
        $this->load();
        return $this->allDomains;
    }

    public function getDomainCatalogue($locale, $domain)
    {
        $this->load();
        $catalogue = new MessageCatalogue($locale);
        $translations = array();
        foreach ($this->unitsPerDomainAndKey as $keys) {
            foreach ($keys as $unit) {
                foreach ($unit->getTranslations() as $t) {
                    if($t->getLocale() == $locale && $t->getDomain() == $domain) {
                        $translations[$t->getKey()] = $t->getValue();
                    }
                }
            }
        }

        $catalogue->add($translations, $domain);
        return $catalogue;
    }

    public function getTranslation($locale, $domain, $key)
    {
        $this->load();
        $translations = $this->getTranslations();
        return array_key_exists($domain, $translations) &&
               array_key_exists($key, $translations[$domain]) &&
               array_key_exists($locale, $translations[$domain][$key]) ? $translations[$domain][$key][$locale] : null;
    }

    public function getAllTranslationUnits()
    {
        $this->load();
        $units = array();
        foreach ($this->unitsPerDomainAndKey as $domain => $keys) {
            foreach($keys as $key => $unit) {
                $units[] = $unit;
            }
        }

        return $units;
    }

    public function addNewTranslation($locale, $domain, $key, $value) {
        $this->updateTranslation($locale, $domain, $key, $value);
    }

    public function updateTranslation($locale, $domain, $key, $value) {
        $this->load();
        if (isset($this->unitsPerDomainAndKey[$domain][$key])) {
            $this->unitsPerDomainAndKey[$domain][$key]->setTranslation($locale, $value);
        } else {
            throw new RuntimeException("This unit does not exists");
        }
    }

}