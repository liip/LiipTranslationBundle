<?php

namespace Liip\TranslationBundle\Model;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Model
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Translation {
    /** @var string */
    private $value;
    /** @var string */
    private $locale;
    /** @var Unit */
    private $unit;

    /**
     * @param string $t value (ie translation)
     * @param string $l locale
     * @param Unit $u unit related to this translation
     */
    public function __construct($t, $l, Unit $u)
    {
        $this->value = $t;
        $this->locale = $l;
        $this->unit = $u;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($t)
    {
        $this->value = $t;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getUnit()
    {
        return $this->unit;
    }

    public function getKey()
    {
        return $this->getUnit()->getTranslationKey();
    }

    public function getDomain()
    {
        return $this->getUnit()->getDomain();
    }

    public function __toString()
    {
        return $this->getValue();
    }
}