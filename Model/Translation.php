<?php

namespace Liip\TranslationBundle\Model;

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