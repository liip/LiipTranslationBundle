<?php

namespace Liip\TranslationBundle\Model;

/**
 * A Unit is a collection of translations for a given domain and translation key.
 *
 * The various translations can be accessed through a variety of method :
 * 1° Using the dedicated methods to retrieve Translation objects
 * 2° As an array, directly retrieving string values
 * 3° Using foreach to iterate over Translation objects
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
class Unit extends Persistent implements \Iterator, \ArrayAccess
{
    /** @var string */
    private $domain;
    /** @var string */
    private $key;
    /** @var array */
    private $metadata = array();
    /** @var Translation[] translations in various locales */
    private $translations = array();

    public function __construct($domain, $key, array $metadata = array(), $isNew = true)
    {
        $this->domain = $domain;
        $this->key = $key;
        $this->metadata = $metadata;

        if($isNew) {
            $this->setIsNew(true);
        }
    }

    /**
     * @param array $m the new metadata
     */
    public function setMetadata(array $metadata = array())
    {
        if ($metadata == $this->getMetadata()) {
            return;
        }

        $this->metadata = $metadata;
        $this->setIsModified(true);
    }

    /**
     * @return array metadata as array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set a translation for the given locale without caring if
     * the translation already exists or not.
     *
     * @param string $locale the locale
     * @param string $translation the translation (value)
     */
    public function setTranslation($locale, $translation, $isUpdate = true)
    {
        $this->offsetSet($locale, $translation);
        if($isUpdate) {
            $this->setIsModified(true);
        }
    }

    /**
     * Add or set the translation for the locale associated to the
     * given translation.
     *
     * @param Translation $translation
     */
    public function addTranslation(Translation $translation, $isUpdate = true)
    {
        $this->translations[$translation->getLocale()] = $translation;
        if($isUpdate) {
            $this->setIsModified(true);
        }
    }

    /**
     * @return string help message based on the metadata
     */
    public function getHelp()
    {
        if (count($this->metadata) == 0) {
            return '-';
        }
        $metadata = $this->getMetadata();
        unset($metadata['id']);

        $text = '';
        foreach($metadata as $key => $value) {
            if ($key == 'note'){
                $text .= $value;
            }
        }

        return $text;
    }

    /**
     * @return string the domain of this unit
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string the translation key associated with this unit
     */
    public function getTranslationKey()
    {
        return $this->key;
    }

    /**
     * @return Translation[] all translations for this unit as an array indexed by locale
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Retrieve the translation for a particular locale, or false if the
     * translation does not exists.
     *
     * @param string $locale
     * @return null|Translation
     */
    public function getTranslation($locale)
    {
        if(array_key_exists($locale, $this->translations)) {
            return $this->translations[$locale];
        }
        return null;
    }

    /**
     * @param string $locale
     * @return bool does a translation exists for the given locale ?
     */
    public function hasTranslation($locale)
    {
        return $this->offsetExists($locale);
    }

    public function deleteTranslation($locale)
    {
        $this->getTranslation($locale)->delete();
        $this->setIsModified();
    }


    public function offsetExists($locale)
    {
        return array_key_exists($locale, $this->translations) && ! $this->translations[$locale]->isDeleted;
    }

    public function offsetGet($locale)
    {
        if($this->offsetExists($locale)) {
            return $this->translations[$locale]->getValue();
        }
        return false;
    }

    public function offsetSet($locale, $value)
    {
        $this->setIsModified(true);
        if($this->offsetExists($locale)) {
            $this->translations[$locale]->setValue($value);
            $this->translations[$locale]->setIsModified(true);
        } else {
            if (!$locale) {
                throw new \RuntimeException("cannot set a translation without locale.");
            }
            $t = new Translation($value, $locale, $this);
            $t->setIsNew(true);
            $this->translations[$locale] = $t;
        }
        return true;
    }

    public function offsetUnset($locale)
    {
        $this->deleteTranslation($locale);
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
