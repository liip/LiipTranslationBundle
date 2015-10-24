<?php

namespace Liip\TranslationBundle\Repository;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Model\Exceptions\PermissionDeniedException;
use Liip\TranslationBundle\Persistence\NotFoundException;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Translation\Translator;
use Liip\TranslationBundle\Security\Security;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Allow to retrieve, filter and persist translation unit.
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
class UnitRepository
{
    /** @var array $config The injected config */
    protected $config = array();

    /** @var Translator */
    protected $translator;

    /** @var PersistenceInterface $persistence */
    protected $persistence;

    /** @var Unit[] $allUnits  */
    private $allUnits = array();

    /** @var Security $security */
    protected $security;

    private $loaded = false;

    public function __construct($config, Translator $translator, PersistenceInterface $persistence, Security $security)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->persistence = $persistence;
        $this->security = $security;
    }

    /**
     * Return the list of managed locales (defined in the bundle config).
     *
     * @return array
     */
    public function getLocaleList()
    {
        return $this->config['locale_list'];
    }

    protected function loadAll()
    {
        if ($this->loaded) {
            return;
        }

        $units = $this->persistence->getUnits();
        // if we already added units to the repository, those we get
        // from the persistence should override the created ones
        if (count($this->allUnits) > 0) {
            foreach ($this->allUnits as $u) {
                $found = false;
                foreach ($units as $u2) {
                    if ($u->getDomain() == $u2->getDomain() && $u->getKey() == $u2->getKey()) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $units[] = $u;
                }
            }
        }
        $this->allUnits = $units;
        $this->loaded = true;
    }

    /**
     * @return Unit[]
     */
    public function findAll()
    {
        $this->loadAll();

        return $this->allUnits;
    }

    public function getAll()
    {
        return $this->findAll();
    }

    public function getAllByDomainAndKey()
    {
        $units = array();
        foreach ($this->getAll() as $unit) {
            $units[$unit->getDomain()][$unit->getKey()] = $unit;
        }

        return $units;
    }

    /**
     * @param array|string $columns
     * @param string|null  $value
     *
     * @return Unit[]
     */
    public function findBy($columns, $value = null)
    {
        $this->loadAll();

        if (!is_array($columns)) {
            $columns = array($columns => $value);
        }

        $result = array();
        foreach ($this->allUnits as $u) {
            $status = true;
            foreach ($columns as $column => $value) {
                $unitValue = call_user_func(array($u, 'get'.ucfirst($column)));
                $status &= is_array($value) ? in_array($unitValue, $value) : $unitValue == $value;
            }
            if ($status) {
                $result[] = $u;
            }
        }

        return $result;
    }

    /**
     * @param string $value
     *
     * @return Unit[]
     */
    public function findByDomain($value)
    {
        return $this->findBy('domain', $value);
    }

    /**
     * @param string $value
     *
     * @return Unit[]
     */
    public function findByKey($value)
    {
        return $this->findBy('key', $value);
    }

    /**
     * @param string $domain
     * @param string $key
     *
     * @return Unit
     */
    public function findByDomainAndKey($domain, $key)
    {
        return $this->persistence->getUnit($domain, $key);
    }

    /**
     * @param string $domain
     * @param string $key
     * @param string $locale
     * @param bool   $nullWhenNotFound
     *
     * @return Translation
     */
    public function findTranslation($domain, $key, $locale, $nullWhenNotFound = false)
    {
        // Get the unit
        try {
            $unit = $this->findByDomainAndKey($domain, $key);
        } catch (NotFoundException $e) {
            if ($nullWhenNotFound) {
                return;
            }
            throw $e;
        }

        // Check translation
        if ($unit && $unit->hasTranslation($locale)) {
            return $unit->getTranslation($locale);
        }

        return;
    }

    public function createUnit($domain, $key, array $metadata = array())
    {
        $u = new Unit($domain, $key, $metadata);
        $this->allUnits[] = $u;

        return $u;
    }

    public function persist($objects = null)
    {
        if ($objects === null) {
            $objects = $this->allUnits;
        }
        if ($objects instanceof Unit) {
            $objects = array($objects);
        }

        $dirtyUnits = array(
            'deleted' => array(),
            'modified' => array(),
            'created' => array(),
        );

        $dirtyTranslations = array(
            'deleted' => array(),
            'modified' => array(),
            'created' => array(),
        );

        foreach ($objects as $unit) {
            if ($unit->isDirty()) {
                $this->checkDomainGrants($unit->getDomain());
                $dirtyUnits[$unit->getDirtyReason()][] = $unit;
                foreach ($unit->getTranslations() as $translation) {
                    if ($translation->isDirty()) {
                        $this->checkLocaleGrants($translation->getLocale());
                        $dirtyTranslations[$translation->getDirtyReason()][] = $translation;
                    }
                }
            }
        }

        $this->persistence->saveUnits(
            $dirtyUnits['modified'] + $dirtyUnits['created']
        );

        $this->persistence->saveTranslations(
            $dirtyTranslations['modified'] + $dirtyTranslations['created']
        );
        $this->persistence->deleteTranslations($dirtyTranslations['deleted']);

        $this->persistence->deleteUnits($dirtyUnits['deleted']);

        // Return statistics
        $stats = array(
            'units' => array(
                'deleted' => count($dirtyUnits['deleted']),
                'created' => count($dirtyUnits['created']),
                'updated' => count($dirtyUnits['modified']),
            ),
            'translations' => array(
                'deleted' => count($dirtyTranslations['deleted']),
                'created' => count($dirtyTranslations['created']),
                'updated' => count($dirtyTranslations['modified']),
            ),
        );
        $stats['units']['text'] = $this->generateStatisticText($stats['units']);
        $stats['translations']['text'] = $this->generateStatisticText($stats['translations']);

        return $stats;
    }

    protected function generateStatisticText($stats)
    {
        return sprintf('%s created, %s modified and %s removed', $stats['created'], $stats['updated'], $stats['deleted']);
    }

    /**
     * @return array
     */
    public function getDomainList()
    {
        $this->loadAll();

        return array_values(array_unique(array_map(function (Unit $u) { return $u->getDomain(); }, $this->allUnits)));
    }

    /**
     * @param string $locale
     * @param string $domain
     *
     * @return MessageCatalogue
     */
    public function getMessageCatalogues($locale, $domain)
    {
        $catalogue = new MessageCatalogue($locale);
        $units = $this->findByDomain($domain);
        $translations = array();
        foreach ($units as $unit) {
            if ($unit->hasTranslation($locale)) {
                $translations[$unit->getKey()] = $unit->getTranslation($locale)->getValue();
            }
        }

        $catalogue->add($translations, $domain);

        return $catalogue;
    }

    /**
     * Remove a specific translation.
     *
     * @param string $locale
     * @param string $domain
     * @param string $key
     */
    public function removeTranslation($locale, $domain, $key)
    {
        $unit = $this->findByDomainAndKey($domain, $key);
        $unit->deleteTranslation($locale);
        $this->persist($unit);
    }

    /**
     * Update a specific translation.
     *
     * @param string $locale
     * @param string $domain
     * @param string $key
     * @param string $value
     */
    public function updateTranslation($locale, $domain, $key, $value)
    {
        $unit = $this->findByDomainAndKey($domain, $key);
        $unit->setTranslation($locale, $value);
        $this->persist($unit);
    }

    /**
     * @param array $filters
     *
     * @return Unit[]
     */
    public function findFiltered(array $filters)
    {
        /* @var Unit[] $units */
        if (!isset($filters['domain']) || is_null($filters['domain']) || empty($filters['domain'])) {
            $units = $this->findAll();
        } else {
            $units = $this->findByDomain($filters['domain']);
        }

        foreach ($units as $k => $u) {
            $filterEmpty = isset($filters['empty']) && $filters['empty'];
            $filterKey = isset($filters['key']) && strlen(trim($filters['key'])) > 0 ? trim($filters['key']) : null;
            $filterValue = isset($filters['value']) && strlen(trim($filters['value'])) > 0 ? trim($filters['value']) : null;
            if (empty($filters['locale'])) {
                $filters['locale'] = $this->getLocaleList();
            }

            if ($filterKey && strpos($u->getKey(), $filterKey) === false) {
                unset($units[$k]);
                continue;
            }

            $nonEmptyCount = 0;
            $valueCount = 0;
            foreach ($u->getTranslations() as $t) {
                // Remove translations not required
                if (!in_array($t->getLocale(), $filters['locale'])) {
                    unset($u[$t->getLocale()]);
                    continue;
                }

                // Count empty or match values
                $value = $t->getValue();
                if ($value !== null && strlen($value) > 0) {
                    ++$nonEmptyCount;
                }
                if ($filterValue && strpos($value, $filterValue) !== false) {
                    ++$valueCount;
                }
            }

            // For empty filtering, remove unit with non empty translations
            if ($filterEmpty && $nonEmptyCount >= count($filters['locale'])) {
                unset($units[$k]);
                continue;
            }

            // For value filtering, remove unit when there is no match
            if ($filterValue && $valueCount == 0) {
                unset($units[$k]);
                continue;
            }
        }

        return $units;
    }

    protected function checkDomainGrants($domain)
    {
        if (!$this->security->isGrantedForDomain($domain)) {
            throw new PermissionDeniedException("No rights to update domain [$domain]");
        }
    }

    protected function checkLocaleGrants($locale)
    {
        if (!$this->security->isGrantedForLocale($locale)) {
            throw new PermissionDeniedException("No rights to update locale [$locale]");
        }
    }
}
