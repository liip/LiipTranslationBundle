<?php

namespace Liip\TranslationBundle\Storage\Persistence;

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
interface PersistenceInterface
{
    public function load();

    public function getUnits();
    public function setUnits($units);

    public function getTranslations();
    public function setTranslations($translations);

    public function save();
}