<?php

namespace Liip\TranslationBundle\Persistence;

use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Symfony\Component\Locale\Exception\NotImplementedException;
use Symfony\Component\Yaml\Yaml;

/**
 * To be completed
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

class YamlFilePersistence implements PersistenceInterface
{

    protected $directory;

    public function __construct($options)
    {
        // Handle folder location and create it if not exist
        $this->directory = $options['folder'];
        if (!is_dir($this->directory)) {
            exec('mkdir -p '.$this->directory);
            if (!is_dir($this->directory)) {
                throw new \RuntimeException("Invalid folder [$this->directory] for translation persistence");
            }
        }
    }

    /**
     * @inheritdoc
     * @return \Liip\TranslationBundle\Model\Unit[]
     */
    public function getUnits()
    {
        list($unitData, $translations) = $this->loadFiles();

        $units = array();
        foreach($unitData as $domain => $keys) {
            foreach($keys as $key => $metadata) {
                $units[] = $this->createUnitObject($domain, $key, $metadata, $translations);
            }
        }

        return $units;
    }

    /**
     * @inheritdoc
     * @return Unit
     * @throw NotFoundException
     */
    public function getUnit($domain, $key)
    {
        list($units, $translations) = $this->loadFiles();

        if (!isset($units[$domain][$key])) {
            throw new NotFoundException($domain, $key);
        }

        return $this->createUnitObject($domain, $key, $units[$domain][$key], $translations);
    }



    public function saveUnit(Unit $unit)
    {
        echo 'save unit';
        $this->saveUnits(array($unit));
    }

    public function saveUnits(array $units)
    {
        echo 'save units';
        $existingUnits = $this->loadFile('units');
        foreach ($units as $unit) {
            $existingUnits[$unit->getDomain()][$unit->getTranslationKey()] = $unit->getMetadata();
        }
        $this->dumpFile('units', $existingUnits);
    }


    public function deleteUnit(Unit $unit)
    {
        echo 'delete unit';
        $this->deleteUnits(array($unit));
    }

    public function deleteUnits(array $units)
    {
        echo 'delete units';
        $existingUnits = $this->loadFile('units');
        foreach ($units as $unit) {
            unset($existingUnits[$unit->getDomain()][$unit->getTranslationKey()]);
        }
        $this->dumpFile('units', $existingUnits);
    }



    public function saveTranslation(Translation $translation)
    {
        echo 'save t';
        $this->saveTranslations(array($translation));
    }

    public function saveTranslations(array $translations)
    {
        echo 'save ts '.count($translations);

        $existingTranslations = $this->loadFile('translations');
        foreach ($translations as $t) {
            var_dump(array($t->getValue(), $t->getMetadata()));
            $existingTranslations[$t->getDomain()][$t->getKey()][$t->getLocale()] = array($t->getValue(), $t->getMetadata());
        }
        $this->dumpFile('translations', $existingTranslations);
    }


    public function deleteTranslation(Translation $translation)
    {
        echo 'delete t';
        $this->deleteTranslations(array($translation));
    }

    public function deleteTranslations(array $translations)
    {
        echo 'delete ts';
        $existingTranslations = $this->loadFile('translations');
        foreach ($translations as $t) {
            unset($existingTranslations[$t->getDomain()][$t->getKey()][$t->getLocale()]);
        }
        $this->dumpFile('translations', $existingTranslations);
    }


    protected function createUnitObject($domain, $key, $metadata, $translations)
    {
        $unit = new Unit($domain, $key, $metadata);
        if (isset($translations[$domain][$key])) {
            foreach($translations[$domain][$key] as $locale => $data) {
                list($value, $metadata) = $data;
                $unit->addTranslation(new Translation($value, $locale, $unit, $metadata));
            }
        }

        return $unit;
    }


    protected function loadFiles()
    {
        return array($this->loadFile('units'), $this->loadFile('translations'));
    }

    protected function loadFile($name)
    {
        $file = $this->directory.'/'.$name;
        return file_exists($file) ? Yaml::parse(file_get_contents($file)) : array();
    }

    protected function dumpFile($name, $data)
    {
        file_put_contents($this->directory.'/'.$name, Yaml::dump($data, 4));
    }

}