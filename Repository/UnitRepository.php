<?php

namespace Liip\TranslationBundle\Repository;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Translation\MessageCatalogue;
use Liip\TranslationBundle\Translation\Translator;


/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Repository
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class UnitRepository
{
    protected $config = array();
    /** @var Translator */
    protected $translator;
    /** @var PersistenceInterface $persistence */
    protected $persistence;

    public function __construct($config, Translator $translator, PersistenceInterface $persistence)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->persistence = $persistence;
    }

    /**
     * Return the list of managed locales (defined in the bundle config)
     *
     * @return array
     */
    public function getLocaleList()
    {
        return $this->config['locale_list'];
    }

    /** @var Unit[] $units  */
    private $units = null;
    private $loaded = false;

    protected function load() {
        if($this->loaded) {
            return;
        }

        $this->units = $this->persistence->getUnits();
        $this->loaded = true;
    }

    /**
     * @return Unit[]
     */
    public function findAll()
    {
        $this->load();
        return $this->units;
    }

    /**
     * @param $columns
     * @param null $value
     * @return Unit[]
     */
    public function findBy($columns, $value = null)
    {
        $this->load();

        if(! is_array($columns)) {
            $columns = array($columns => $value);
        }

        $result = array();
        foreach($this->units as $u) {
            $status = true;
            foreach($columns as $column => $value) {
                $unitValue = call_user_func(array($u, 'get' . ucfirst($column)));
                $status &= is_array($value) ? in_array($unitValue, $value) : $unitValue == $value;
            }
            if($status) {
                $result[] = $u;
            }
        }
        return $result;
    }

    /**
     * @param $value
     * @return Unit[]
     */
    public function findByDomain($value) {
        return $this->findBy('domain', $value);
    }

    /**
     * @param $value
     * @return Unit[]
     */
    public function findByTranslationKey($value)
    {
        return $this->findBy('translationKey', $value);
    }

    /**
     * @param $domain
     * @param $key
     * @return Unit
     */
    public function findByDomainAndTranslationKey($domain, $key)
    {
        $result = $this->findBy(array('translationKey' => $key, 'domain' => $domain));
        return reset($result);
    }

    /**
     * @param $domain
     * @param $key
     * @param $locale
     * @return Translation
     */
    public function findTranslation($domain, $key, $locale)
    {
        $unit = $this->findByDomainAndTranslationKey($domain, $key);
        if(isset($unit[$locale])) {
            return $unit[$locale];
        }
        return null;
    }

    /**
     * @param Unit|Unit[] $units persists the unit or units
     */
    public function persist($units) {
        // FIXME : once the persistence is plainy functionnal, do the following
        /*
        if(! is_array($units)) {
            $this->persistence->saveUnit($units);
        } else {
            $this->persistence->saveUnits($units);
        }
        */
        $this->persistence->saveUnits($this->units);
    }

    /**
     * @return array
     */
    public function getDomainList()
    {
        $this->load();
        return array_unique(array_map(function(Unit $u) { return $u->getDomain(); }, $this->units));
    }

    /**
     * @param $locale
     * @param $domain
     * @return MessageCatalogue
     */
    public function getMessageCatalogues($locale, $domain)
    {
        $catalogue = new MessageCatalogue($locale);
        $units = $this->findByDomain($domain);
        $translations = array();
        foreach($units as $u) {
            if(isset($u[$locale])) {
                $translations[$u->getTranslationKey()] = $u->getTranslation($locale)->getValue();
            }
        }

        $catalogue->add($translations, $domain);
        return $catalogue;
    }

    /**
     * @param $locale
     * @param $domain
     * @param $key
     */
    public function removeTranslation($locale, $domain, $key)
    {
        $unit = $this->findByDomainAndTranslationKey($domain, $key);
        unset($unit[$locale]);
        $this->persist($unit);
    }

    /**
     * @param $filters
     * @return Unit[]
     */
    public function findFiltered(array $filters)
    {
        /** @var Unit[] $units */
        if (!isset($filters['domain']) || is_null($filters['domain']) || empty($filters['domain'])) {
            $units = $this->findAll();
        } else {
            $units = $this->findByDomain($filters['domain']);
        }

        foreach ($units as $k => $u) {
            $filterEmpty = isset($filters['empty']) && $filters['empty'];
            $filterKey = isset($filters['key']) && strlen(trim($filters['key'])) > 0 ? trim($filters['key']) : null;
            $filterValue = isset($filters['value']) && strlen(trim($filters['value'])) > 0 ? trim($filters['value']) : null;

            if ($filterKey && strpos($u->getTranslationKey(), $filterKey) === false) {
                unset($units[$k]);
                continue;
            }

            $count = 0;
            $valueCount = 0;
            foreach ($u->getTranslations() as $t) {
                if(! in_array($t->getLocale(), $filters['locale'])) {
                    unset($u[$t->getLocale()]);
                    continue;
                }
                $value = trim($t->getValue());
                if (strlen($value) == 0) {
                    ++$count;
                }

                if ($filterValue && strpos($value, $filterValue) !== false) {
                    ++$valueCount;
                }
            }
            if (($filterEmpty && $count == 0) || ($filterValue && $valueCount == 0)) {
                unset($units[$k]);
            }
        }

        return $units;
    }
}