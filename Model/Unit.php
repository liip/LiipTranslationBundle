<?php
/**
 * User: dj
 * Date: 17.09.13
 */

namespace Liip\TranslationBundle\Model;


class Unit {

    public $domain;
    public $key;
    public $metadata = array();
    protected $translations = array();

    public function setTranslation($locale, $translation)
    {
        $this->translations[$locale] = $translation;
    }

    public function getHelp()
    {
        if (count($this->metadata) == 0) {
            return '-';
        }
        return var_export($this->metadata, true);

    }

    public function getTranslation($locale)
    {
        return array_key_exists($locale, $this->translations) ? $this->translations[$locale] : null;
    }


}