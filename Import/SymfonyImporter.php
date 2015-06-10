<?php

/**
 * Importer for the classic symfony files, used by the command app/console translation:import
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Import
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
namespace Liip\TranslationBundle\Import;

use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyImporter
{
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
     * @param array $resource
     *
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
            'domain_list' => null,
            'output' => null,
            'import-translations' => false,
            'override' => false,
            'metadata_locale' => 'en', // define from which locale you want to import the metadata
            'prune' => false,
        ), $options);
        if (array_key_exists('output', $options)) {
            $this->logger = $options['output'];
        }
        $locales = $options['locale_list'] !== null ? $options['locale_list'] : $this->getLocaleList();

        // Create all catalogues
        /** @var MessageCatalogue[] $catalogues */
        $catalogues = array();
        foreach ($locales as $locale) {
            $catalogues[$locale] = new MessageCatalogue($locale);
        }

        // Import resources one by one
        $this->log("<info>Evaluation of the file list</info>\n");
        foreach ($this->getStandardResources() as $resource) {
            $this->log("    Resource <fg=cyan>{$resource['path']}</fg=cyan>");

            if ($this->checkIfResourceIsIgnored($resource)) {
                $this->log("  >> <comment>Skipped</comment> (due to ignore settings from the config)\n");
                continue;
            }

            if (!in_array($resource['locale'], $locales)) {
                $this->log("  >> <comment>Skipped</comment> (unwanted locales)\n");
                continue;
            }

            if ($options['domain_list'] !== null && !in_array($resource['domain'], $options['domain_list'])) {
                $this->log("  >> <comment>Skipped</comment> (unwanted domain)\n");
                continue;
            }

            $catalogues[$resource['locale']]->addCatalogue($this->translator->loadResource($resource));
            $this->log("  >> <info>OK</info>\n");
        }

        // Update all units from the catalog
        $this->log("\n<info>Update or create units and associated translations</info>\n");
        $existingUnits = $this->repository->getAllByDomainAndKey();
        $allFileUnits = array();
        foreach ($locales as $locale) {
            foreach ($catalogues[$locale]->all() as $domain => $translations) {
                foreach ($translations as $key => $value) {
                    // Retrieved or create a unit
                    if (!isset($existingUnits[$domain][$key])) {
                        $this->log("\t>> Creation of a new Unit [$domain, $key]\n");
                        $existingUnits[$domain][$key] = $this->repository->createUnit($domain, $key);
                    }
                    $unit = $existingUnits[$domain][$key];

                    // Update it's metadata
                    if ($locale == $options['metadata_locale']) {
                        $catalogMetadata = $catalogues[$locale]->getMetadata($key, $domain);
                        if ($catalogMetadata !== $unit->getMetadata()) {
                            $unit->setMetadata(is_null($catalogMetadata) ? array() : $catalogMetadata);
                            $this->log("\t>> Metadata of the Unit [$domain, $key] updated\n");
                        }
                    }

                    // Update translation
                    if ($options['import-translations']) {
                        if ($unit->hasTranslation($locale) && $options['override']) {
                            $this->log("\t>> Translation [$domain, $key] for [$locale] overridden by '$value'\n");
                            $unit->setTranslation($locale, $value);
                        }
                        if (!$unit->hasTranslation($locale)) {
                            $this->log("\t>> Translation [$domain, $key] for [$locale] imported\n");
                            $unit->setTranslation($locale, $value);
                        }
                    }

                    // Key a trace of all units from the file for the prune process
                    $allFileUnits[$domain][$key] = true;
                }
            }
        }

        // Potentially remove no more existing units
        if ($options['prune'] === true) {
            $this->log("\n<info>Remove units that are no more present in translation files</info>\n");
            $domains = $options['domain_list'] !== null ? $options['domain_list'] : array_keys($existingUnits);
            foreach ($domains as $domain) {
                if (!isset($allFileUnits[$domain])) {
                    continue;
                }
                $removed = array_diff(array_keys($existingUnits[$domain]), array_keys($allFileUnits[$domain]));
                foreach ($removed as $key) {
                    $this->log("\t>> Translation unit [$domain, $key] removed\n");
                    $existingUnits[$domain][$key]->delete();
                }
            }
        }

        $this->log("<info>Save the changes</info>\n");
        $this->log("<comment>Persisting</comment> ... ");
        $stat = $this->repository->persist();
        $this->log("<info>Success</info>\n");

        return $stat;
    }

    protected function log($msg)
    {
        if ($this->logger && $this->logger->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $this->logger->write($msg);
        }
    }
}
