<?php

namespace Liip\TranslationBundle\Model\Storage\Persistence;

interface PersistenceInterface
{
    public function load();

    public function getUnits();
    public function setUnits($units);

    public function getTranslations();
    public function setTranslations($translations);

    public function save();
}