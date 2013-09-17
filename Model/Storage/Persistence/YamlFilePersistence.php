<?php

namespace Liip\TranslationBundle\Model\Storage\Persistence;

use Liip\TranslationBundle\Model\Storage\Persistence\PersistenceInterface;
use Symfony\Component\Yaml\Yaml;


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