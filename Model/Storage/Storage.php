<?php
/**
 * User: dj
 * Date: 13.09.13
 */

namespace Liip\TranslationBundle\Model\Storage;

use Liip\TranslationBundle\Model\Storage\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Model\Unit;
use Symfony\Component\Translation\MessageCatalogue;

class Storage {

    /** @var  PersistenceInterface */
    protected $persistence;
    protected $loaded = false;
    protected $units;
    protected $translations;

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
        $this->persistence->load();
        $this->units = $this->persistence->getUnits();
        $this->translations = $this->persistence->getTranslations();
        $this->loaded = true;
    }

    /**
     * Save the current unit and translation into the Persistence
     */
    public function save() {
        $this->persistence->setTranslations($this->translations);
        $this->persistence->setUnits($this->units);
        $this->persistence->save();
    }

    /**
     * Create or update a translation unit
     * @param $domain
     * @param $key
     * @param $metadata
     */
    public function createOrUpdateTranslationUnit($domain, $key, $metadata)
    {
        $this->load();
        $this->units[$domain][$key] = $metadata;
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
        $this->translations[$locale][$domain][$key] = $value;
    }

    public function getTranslations()
    {
        $this->load();
        return $this->translations;
    }

    public function getDomainCatalogue($locale, $domain)
    {
        $this->load();
        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($this->translations[$locale][$domain], $domain);
        return $catalogue;
    }

    public function getTranslation($locale, $domain, $key)
    {
        $this->load();
        return array_key_exists($locale, $this->translations) && array_key_exists($domain, $this->translations[$locale]) && array_key_exists($key, $this->translations[$locale][$domain]) ? $this->translations[$locale][$domain][$key] : null;
    }

    public function getAllTranslationUnits()
    {
        $this->load();
        $units = array();
        foreach ($this->units as $domain => $unitData) {
            foreach ($unitData as $key => $metadata) {
                $unit = new Unit();
                $unit->domain = $domain;
                $unit->key = $key;
                $unit->metadata = $metadata;
                $units[] = $unit;
                foreach($this->translations as $locale => $translations) {
                    if (isset($translations[$domain][$key])){
                        $unit->setTranslation($locale, $translations[$domain][$key]);
                    }
                }
            }
        }

        return $units;
    }

    public function addNewTranslation($locale, $domain, $key, $newValue) {
        $this->load();
        $this->translations[$locale][$domain][$key] = $newValue;
    }

    public function updateTranslation($locale, $domain, $key, $value) {
        $this->load();
        $this->translations[$locale][$domain][$key] = $value;
    }

}