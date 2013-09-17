<?php
/**
 * User: dj
 * Date: 13.09.13
 */

namespace Liip\TranslationBundle\Model\Storage;


use Liip\TranslationBundle\Model\Storage\Persistence\FilePersistence;
use Liip\TranslationBundle\Model\Storage\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Model\Storage\Persistence\YamlFilePersistence;
use Liip\TranslationBundle\Model\Unit;
use Symfony\Component\Translation\MessageCatalogue;

class Storage {

    /** @var  PersistenceInterface */
    protected $persistence;
    protected $units;
    protected $translations;

    public function __construct(PersistenceInterface $persistence) {
        $this->persistence = $persistence;
    }

    public function load() {
        $this->persistence->load();
        $this->units = $this->persistence->getUnits();
        $this->translations = $this->persistence->getTranslations();
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
        if (!array_key_exists($domain, $this->units)) {
            $this->units[$domain] = array();
        }
        $this->units[$domain][$key] = $metadata;
    }

    /**
     * Set a translation value, only if it's currently empty
     * @param $locale
     * @param $domain
     * @param $key
     * @param $value
     */
    public function setBaseTranslation($locale, $domain, $key , $value)
    {
        if (!array_key_exists($locale, $this->translations)) {
            $this->translations[$locale] = array();
        }
        if (!array_key_exists($domain, $this->translations[$locale])) {
            $this->translations[$locale][$domain] = array();
        }
        if (!array_key_exists($key, $this->translations[$locale][$domain])) {
            $this->translations[$locale][$domain][$key] = $value;
        }
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
}