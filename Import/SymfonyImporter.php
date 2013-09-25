<?php
/**
 * Created by JetBrains PhpStorm.
 * User: krtek
 * Date: 9/25/13
 * Time: 10:56 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Liip\TranslationBundle\Import;


use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Translation\MessageCatalogue;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyImporter {
    private $config;
    /** @var Translator $translator */
    private $translator;
    /** @var PersistenceInterface $persistence */
    private $persistence;
    /** @var OutputInterface $logger */
    private $logger;

    public function __construct($config, Translator $translator, PersistenceInterface $persistence)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->persistence = $persistence;
    }

    /**
     * Return the list of managed locales (defined in the bundle config)
     *
     * @return array
     */
    public function getLocaleList()
    {
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
     * @param $resource
     * @return boolean
     */
    public function checkIfResourceIsIgnored($resource)
    {
        // TODO, implement something from the bundle config
        return strpos($resource['path'], 'symfony/symfony') !== false;
    }

    public function processImportOfStandardResources($options)
    {
        if (array_key_exists('logger', $options)){
            $this->logger = $options['logger'];
        }

        $locales = array_key_exists('locale_list', $options) ? $options['locale_list'] : $this->getLocaleList();

        $this->log("<info>Start importation for locales:</info> [".implode(', ', $locales)."]\n");

        // Create all catalogues
        /** @var MessageCatalogue[] $catalogues */
        $catalogues = array();
        foreach ($locales as $locale) {
            $catalogues[$locale] = new MessageCatalogue($locale);
        }

        // Import resources one by one
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

            $catalogues[$resource['locale']]->addCatalogue($this->translator->loadResource($resource));
            $this->log("  >> <comment>OK</comment>\n");

        }

        $unitsPerDomainAndKey = array();
        // Load translations into the intermediate persistence
        foreach ($locales as $locale) {
            foreach ($catalogues[$locale]->all() as $domain => $translations) {
                $this->log("\n  Import catalog <comment>$domain</comment>\n");
                foreach($translations as $key => $value) {
                    $this->log("    >> key [$key] with a base value of [$value]\n");
                    $metadata = $catalogues[$locale]->getMetadata($key, $domain);

                    if(! isset($unitsPerDomainAndKey[$domain][$key])) {
                        $unitsPerDomainAndKey[$domain][$key] = new Unit($domain, $key, is_null($metadata) ? array() : $metadata);
                    }
                    $unitsPerDomainAndKey[$domain][$key]->setTranslation($locale, $value);
                }
            }
        }

        $units = array();
        foreach($unitsPerDomainAndKey as $keys) {
            foreach($keys as $u) {
                $units[] = $u;
            }
        }

        $this->persistence->saveUnits($units);
        $this->log(" <info>Import success</info>\n");
    }

    protected function log($msg) {
        if ($this->logger) {
            $this->logger->write($msg);
        }
    }

    public function clearSymfonyCache()
    {
        foreach ($this->getLocaleList() as $locale) {
            $this->translator->clearCacheForLocale($locale);
        }
    }
}