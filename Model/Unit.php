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
class Unit implements \IteratorAggregate, \ArrayAccess
{
    /** @var string */
    private $domain;
    /** @var string */
    private $key;
    /** @var array */
    private $metadata = array();
    /** @var Translation[] translations in various locales */
    private $translations = array();

    public function __construct($d, $k, array $m = array())
    {
        $this->domain = $d;
        $this->key = $k;
        $this->metadata = $m;
    }

    public function setTranslation($locale, $translation)
    {
        $this->offsetSet($locale, $translation);
    }

    public function getHelp()
    {
        if (count($this->metadata) == 0) {
            return '-';
        }
        return var_export($this->metadata, true);
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getTranslationKey()
    {
        return $this->key;
    }

    public function getTranslation($locale)
    {
        return $this->offsetGet($locale);
    }

    public function getIterator()
    {
        return $this;
    }

    public function offsetExists($locale)
    {
        return array_key_exists($locale, $this->translations);
    }

    public function offsetGet($locale)
    {
        if($this->offsetExists($locale)) {
            return $this->translations[$locale];
        }
        return false;
    }

    public function offsetSet($locale, $value)
    {
        if($this->offsetExists($locale)) {
            $this->translations[$locale]->setValue($value);
        } else {
            $t = new Translation($value, $locale, $this);
            if($locale) {
                $this->translations[$locale] = $t;
            } else {
                $this->translations[] = $t;
            }
        }
        return true;
    }

    public function offsetUnset($locale)
    {
        unset($this->translations[$locale]);
        return true;
    }

    public function current()
    {
        return current($this->translations);
    }

    public function next()
    {
        return next($this->translations);
    }

    public function key()
    {
        return key($this->translations);
    }

    public function valid()
    {
        return key($this->translations) !== null;
    }

    public function rewind()
    {
        return reset($this->translations);
    }
}