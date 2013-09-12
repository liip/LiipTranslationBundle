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

    /**
     * Return true if a resource must be ignore
     *
     * @return boolean
     */
    public function checkIfResourceIsIgnored($resource)
    {
        return false; // TODO
    }

    public function processImportOfStandardResources($options)
    {
        if (array_key_exists('logger', $options)){
            $this->logger = $options['logger'];
        }

        $locales = array_key_exists('locale_list', $options) ? $options['locale_list'] : $this->getLocaleList();

        $this->log("<info>Start importation for locales:</info> [".implode(', ', $locales)."]\n");

        foreach($this->getStandardResources() as $resource) {

            $this->log("Import resource <info>{$resource['path']}</info>");

            if ($this->checkIfResourceIsIgnored($resource)) {
                $this->log("  >> <comment>Skipped</comment> (due to ignore settings from the config)\n");
                continue;
            }

            if (!in_array($resource['locale'], $locales)) {
                $this->log("  >> <comment>Skipped</comment> (unwanted locales)\n");
                continue;
            }

            /** @var MessageCatalogue $catalog */
            $catalog = $this->translator->loadResource($resource);

            foreach ($catalog->all() as $domain => $translations) {
                $this->log("\n  Import catalog <comment>$domain</comment>\n");
                foreach($translations as $key => $value) {
                    $this->log("    >> new key [$key] with a base value of [$value]\n");
                    $metadata = $catalog->getMetadata($key, $domain);
                    var_dump($metadata);
                    $this->storage->import($domain, $key, $resource['locale'], $metadata, $value);
                }
            }
            $this->log(" <info>Import success</info>\n");

        }
    }

    protected function log($msg) {
        if ($this->logger) {
            $this->logger->write($msg);
        }
    }
}