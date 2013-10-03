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
class Translation extends Persistent {
    /** @var string */
    private $value;
    /** @var string */
    private $locale;
    /** @var Unit */
    private $unit;
    /** @var array */
    private $metadata;

    /**
     * @param string $t value (ie translation)
     * @param string $l locale
     * @param Unit $u unit related to this translation
     */
    public function __construct($value, $locale, Unit $unit, array $metadata = array())
    {
        $this->value = $value;
        $this->locale = $locale;
        $this->unit = $unit;
        $this->metadata = $metadata;
    }

    /**
     * @param array $m the new metadata
     */
    public function setMetadata(array $m = array())
    {
        $this->metadata = $m;
    }

    /**
     * @return array metadata as array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($t)
    {
        $this->value = $t;

        $this->setIsModified(true);
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
