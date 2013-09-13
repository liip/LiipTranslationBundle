<?php

namespace Liip\TranslationBundle\Model\Storage\Repository;

use Liip\TranslationBundle\Model\Storage\Repository\RepositoryInterface;
use Symfony\Component\Yaml\Yaml;


class FileRepository implements RepositoryInterface {

    protected $directory = '/Users/dj/Sites/i18n-sandbox/data/translations';
    protected $loaded = false;
    protected $units = null;
    protected $translations = null;

    function load()
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