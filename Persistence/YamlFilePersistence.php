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

class YamlFilePersistence implements PersistenceInterface {
    protected $directory;

    public function __construct($options)
    {
        $this->directory = $options['folder'];
        if (!is_dir($this->directory)) {
            exec('mkdir -p '.$this->directory);
            if (!is_dir($this->directory)) {
                throw new \RuntimeException("Invalid folder [$this->directory] for translation persistence");
            }
        }
    }

    public function getUnits()
    {
        $units = array();
        $translations = array();

        foreach(array('units', 'translations') as $dataType) {
            $file = $this->directory.'/'.$dataType;
            $$dataType = file_exists($file) ? Yaml::parse(file_get_contents($file)) : array();
        }

        $ret = array();
        foreach($units as $domain => $keys) {
            foreach($keys as $key => $metadata) {
                $unit = new Unit($domain, $key, is_null($metadata) ? array() : $metadata);

                if(isset($translations[$domain][$key])) {
                    foreach($translations[$domain][$key] as $locale => $value) {
                        $unit->setTranslation($locale, $value, false);
                    }
                }
                $ret[] = $unit;
            }
        }

        return $ret;
    }

    /**
     * @param Unit[] $objectUnits
     * @return void
     */
    public function saveUnits(array $objectUnits)
    {
        $translations = array();
        $units = array();
        foreach ($objectUnits as $u) {
            foreach ($u->getTranslations() as $t) {
                $translations[$t->getDomain()][$t->getKey()][$t->getLocale()] = $t->getValue();
            }
            $units[$u->getDomain()][$u->getTranslationKey()] = $u->getMetadata();
        }

        foreach (array('units', 'translations') as $dataType) {
            file_put_contents($this->directory.'/'.$dataType, Yaml::dump($$dataType, 4));
        }
    }

    public function deleteUnits(array $objectUnits)
    {
        throw new NotImplementedException("implement me !");
    }

    public function saveTranslations(array $objectTranslations)
    {
        throw new NotImplementedException("implement me !");
    }

    public function deleteTranslations(array $objectTranslations)
    {
        throw new NotImplementedException("implement me !");
    }

    public function deleteTranslation(Translation $translation)
    {
        throw new NotImplementedException("implement me !");
    }

    public function saveTranslation(Translation $translation)
    {
        throw new NotImplementedException("implement me !");
    }

    public function saveUnit(Unit $unit)
    {
        throw new NotImplementedException("implement me !");
    }

    public function getUnit($domain, $key)
    {
        throw new NotImplementedException("implement me !");
    }
}
