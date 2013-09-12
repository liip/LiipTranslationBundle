<?php

namespace Liip\TranslationBundle\Manager;

use Liip\TranslationBundle\Translation\DualModeTranslator;
use Symfony\Component\Translation\MessageCatalogue;

class Manager
{
    protected $config = array();

    /** @var DualModeTranslator */
    protected $translator;

    protected $logger;

    public function __construct($config, $translator)
    {
        $this->config = $config;
        $this->translator = $translator;
    }

    /**
     * Return the list of managed locales (defined in the bundle config)
     */
    public function getLocaleList() {
        return $this->config['locale_list'];
    }

    /**
     * Return the list of all translations resources
     *
     * @return array
     */
    public function getStandardResources()
    {
        return $this->translator->getStandardResources();
    }

}