<?php

namespace Liip\TranslationBundle\Model\Storage\Repository;

use Liip\TranslationBundle\Model\Translation\Unit;

interface RepositoryInterface
{
    public function load();

    public function getUnits();
    public function setUnits($units);

    public function getTranslations();
    public function setTranslations($translations);

    public function save();
}