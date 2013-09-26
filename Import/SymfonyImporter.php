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

    public function processImportOfStandardResources($options, $override = false)
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

        // Load translations into the intermediate persistence
        $units = array();
        foreach ($locales as $locale) {
            foreach ($catalogues[$locale]->all() as $domain => $translations) {
                $this->log("\nImport catalog <comment>$domain</comment>\n");
                foreach($translations as $key => $value) {
                    $this->log("\t>> key [$key] with a base value of [$value]");
                    $metadata = $catalogues[$locale]->getMetadata($key, $domain);

                    $unit = $this->repository->findByDomainAndTranslationKey($domain, $key);
                    if(! $unit) {
                        $unit = $this->repository->createUnit($domain, $key, is_null($metadata) ? array() : $metadata);
                    }

                    if($unit->offsetExists($locale)) {
                        if($override) {
                            $this->log(" <info>Imported</info> <comment>(overriden current value)</comment>\n");
                        } else {
                            $this->log(" <comment>Skipped (no override)</comment>\n");
                            continue;
                        }
                    } else {
                        $this->log(" <info>Imported</info>\n");
                    }
                    $unit->setTranslation($locale, $value);
                }
            }
        }

        $this->repository->persist();
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