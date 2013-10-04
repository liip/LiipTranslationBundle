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
use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\Translation\MessageCatalogue;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyImporter {
    private $config;
    /** @var Translator $translator */
    private $translator;
    /** @var UnitRepository $repository */
    private $repository;
    /** @var OutputInterface $logger */
    private $logger;

    public function __construct($config, Translator $translator, UnitRepository $repository)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->repository = $repository;
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
        $options = array_merge(array(
            'locale_list' => null,
            'logger' => null,
            'import-translations' => false,
            'override' => false,
            'metadata_locale' => 'en'
        ), $options);
        if (array_key_exists('logger', $options)){
            $this->logger = $options['logger'];
        }
        $locales = $options['locale_list'] !== null ? $options['locale_list'] : $this->getLocaleList();

        $this->log("<info>Start importation for locales:</info> [".implode(', ', $locales)."]\n");

        // Create all catalogues
        /** @var MessageCatalogue[] $catalogues */
        $catalogues = array();
        foreach ($locales as $locale) {
            $catalogues[$locale] = new MessageCatalogue($locale);
        }

        // Import resources one by one
        foreach($this->getStandardResources() as $resource) {
            $this->log("Import resource <fg=cyan>{$resource['path']}</fg=cyan>");

            if ($this->checkIfResourceIsIgnored($resource)) {
                $this->log("  >> <comment>Skipped</comment> (due to ignore settings from the config)\n");
                continue;
            }

            if (!in_array($resource['locale'], $locales)) {
                $this->log("  >> <comment>Skipped</comment> (unwanted locales)\n");
                continue;
            }

            $catalogues[$resource['locale']]->addCatalogue($this->translator->loadResource($resource));
            $this->log("  >> <info>OK</info>\n");

        }

        $existingUnits = $this->repository->getAllByDomainAndKey();

        // Creation of the units
        $units = array();
        foreach ($locales as $locale) {
            foreach ($catalogues[$locale]->all() as $domain => $translations) {
                foreach($translations as $key => $value) {

                    $this->log("    >> Key [$key] for domain [$domain]\n");

                    // Retrieved or create a unit
                    if(!isset($existingUnits[$domain][$key])) {
                        $this->log("\t>> Creation of a new Unit [$domain, $key]\n");
                        $existingUnits[$domain][$key] = $this->repository->createUnit($domain, $key);
                    }
                    $unit = $existingUnits[$domain][$key];

                    // Update it's metadata
                    if ($locale == $options['metadata_locale']) {
                        $catalogMetadata = $catalogues[$locale]->getMetadata($key, $domain);
                        if ($catalogMetadata !== $unit->getMetadata()) {
                            $unit->setMetadata(is_null($metadata) ? array() : $metadata);
                            $this->log("\t>> Metadata of the Unit [$domain, $key] updated\n");
                        }
                    }

                    // Update translation
                    if($options['import-translations']) {
                        if ($unit->hasTranslation($locale) && $options['override']) {
                            $this->log("\t>> Translation in [$locale] overridden\n");
                            $unit->setTranslation($locale, $value);
                        }
                        if (!$unit->hasTranslation($locale)){
                            $this->log("\t>> Translation in [$locale] imported\n");
                            $unit->setTranslation($locale, $value);
                        }
                    }
                }
            }
        }

        $this->log("\n<comment>Persisting</comment> ... ");
        $stat = $this->repository->persist();
        $this->log("<info>Success</info>\n");

        return $stat;
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
