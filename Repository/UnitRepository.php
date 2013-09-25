<?php

namespace Liip\TranslationBundle\Repository;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Translation\MessageCatalogue;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\Security\Core\SecurityContext;


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

    protected $logger;

    /** @var PersistenceInterface $persistence */
    protected $persistence;

    public function __construct($config, $translator, PersistenceInterface $persistence)
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

    public function clearSymfonyCache()
    {
        foreach($this->getLocaleList() as $locale) {
            $this->translator->clearCacheForLocale($locale);
        }
    }

    /**
     * Return the list of all translations resources
     *
     * @return array
     */
    public function getStandardResources()
    {
        return $this->translator->getStandardResources();
    }

    /**
     * Return true if a resource must be ignore
     *
     * @param $resource
     * @return boolean
     */
    public function checkIfResourceIsIgnored($resource)
    {
        // TODO, implement something from the bundle config
        return strpos($resource['path'], 'symfony/symfony') !== false;
    }

    public function processImportOfStandardResources($options)
    {
        if (array_key_exists('logger', $options)){
            $this->logger = $options['logger'];
        }

        $locales = array_key_exists('locale_list', $options) ? $options['locale_list'] : $this->getLocaleList();

        $this->log("<info>Start importation for locales:</info> [".implode(', ', $locales)."]\n");

        // Create all catalogues
        $catalogues = array();
        foreach ($locales as $locale) {
            $catalogues[$locale] = new MessageCatalogue($locale);
        }

        // Import resources one by one
        foreach($this->getStandardResources() as $resource) {

            $this->log("Import resource <info>{$resource['path']}</info>");

            if ($this->checkIfResourceIsIgnored($resource)) {
                $this->log("  >> <comment>Skipped</comment> (due to ignore settings from the config)\n");
                continue;
            }

            if (!in_array($resource['locale'], $locales)) {
                $this->log("  >> <comment>Skipped</comment> (unwanted locales)\n");
                continue;
            }

            $catalogues[$resource['locale']]->addCatalogue($this->translator->loadResource($resource));
            $this->log("  >> <comment>OK</comment>\n");

        }

        $unitsPerDomainAndKey = array();
        // Load translations into the intermediate persistence
        foreach ($locales as $locale) {
            foreach ($catalogues[$locale]->all() as $domain => $translations) {
                $this->log("\n  Import catalog <comment>$domain</comment>\n");
                foreach($translations as $key => $value) {
                    $this->log("    >> key [$key] with a base value of [$value]\n");
                    $metadata = $catalogues[$locale]->getMetadata($key, $domain);

                    if(! isset($unitsPerDomainAndKey[$domain][$key])) {
                        $unitsPerDomainAndKey[$domain][$key] = new Unit($domain, $key, is_null($metadata) ? array() : $metadata);
                    }
                    $unitsPerDomainAndKey[$domain][$key]->setTranslation($locale, $value);
                }
            }
        }

        $units = array();
        foreach($unitsPerDomainAndKey as $keys) {
            foreach($keys as $u) {
                $units[] = $u;
            }
        }

        $this->persistence->saveUnits($units);
        $this->log(" <info>Import success</info>\n");
    }

    protected function log($msg) {
        if ($this->logger) {
            $this->logger->write($msg);
        }
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
                $status &= call_user_func(array($u, 'get' . ucfirst($column))) == $value;
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

    public function getDomainList()
    {
        $this->load();
        return array_unique(array_map(function($u) { return $u->getDomain(); }, $this->units));
    }

    public function getMessageCatalogues($locale, $domain)
    {
        $catalogue = new MessageCatalogue($locale);
        $units = $this->findByDomain($domain);
        $translations = array();
        foreach($units as $u) {
            if(isset($u[$locale])) {
                $translations[$u->getTranslationKey()] = $u[$locale]->getValue();
            }
        }

        $catalogue->add($translations, $domain);
        return $catalogue;
    }
}