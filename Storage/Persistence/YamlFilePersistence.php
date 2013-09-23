<?php

namespace Liip\TranslationBundle\Storage\Persistence;

use Liip\TranslationBundle\Storage\Persistence\PersistenceInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Storage\Persistence
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */

class YamlFilePersistence implements PersistenceInterface {

    protected $directory;
    protected $loaded = false;
    protected $units = null;
    protected $translations = null;

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

    public function load()
    {
        foreach(array('units', 'translations') as $dataType) {
            $file = $this->directory.'/'.$dataType;
            $this->$dataType = file_exists($file) ? Yaml::parse(file_get_contents($file)) : array();
        }
        $this->loaded = true;
    }

    public function getUnits()
    {
        if (!$this->loaded) {
            throw new \RuntimeException("Data not loaded");
        }

        return $this->units;
    }

    public function setUnits($units)
    {
        $this->units = $units;
    }


    public function getTranslations()
    {
        if (!$this->loaded) {
            throw new \RuntimeException("Data not loaded");
        }

        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    function save()
    {
        foreach(array('units', 'translations') as $dataType) {
            file_put_contents($this->directory.'/'.$dataType, Yaml::dump($this->$dataType, 4));
        }
    }

}