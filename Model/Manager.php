<?php

namespace Liip\TranslationBundle\Model;

use Liip\TranslationBundle\Translation\MessageCatalogue;
use Liip\TranslationBundle\Translation\Translator;
use Liip\TranslationBundle\Storage\Storage;
use Symfony\Component\Security\Core\SecurityContext;


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
class Manager
{
    protected $config = array();

    /** @var Translator */
    protected $translator;

    protected $logger;

    /** @var  Storage */
    protected $storage;

    public function __construct($config, $translator, Storage $storage)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->storage = $storage;
    }

    public function isSecuredByDomain()
    {
        return $this->config['security']['by_domain'];
    }

    public function isSecuredByLocale()
    {
        return $this->config['security']['by_locale'];
    }

    public function getRoleForLocale($locale)
    {
        return 'ROLE_TRANSLATOR_LOCALE_'.strtoupper($locale);
    }

    public function getRoleForDomain($domain)
    {
        return 'ROLE_TRANSLATOR_DOMAIN_'.strtoupper($domain);
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
     * Return the list of locales authorized by the provided security context
     *
     * @param SecurityContext $securityContext
     * @return array
     */
    public function getAuthorizedLocaleList(SecurityContext $securityContext)
    {
        if (!$this->isSecuredByLocale()) {
            return $this->getLocaleList();
        }

        $authorizedLocaleList = array();
        foreach ($this->getLocaleList() as $locale) {
            if ($securityContext->isGranted($this->getRoleForLocale($locale))) {
                $authorizedLocaleList[] = $locale;
            }
        }
    }

    public function clearSymfonyCache()
    {
        foreach($this->getLocaleList() as $locale) {
            $this->translator->clearCacheForLocale($locale);
        }
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
        }

        // Load translations into the intermediate storage
        foreach ($locales as $locale) {
            foreach ($catalogues[$locale]->all() as $domain => $translations) {
                $this->log("\n  Import catalog <comment>$domain</comment>\n");
                foreach($translations as $key => $value) {
                    $this->log("    >> key [$key] with a base value of [$value]\n");
                    $this->storage->createOrUpdateTranslationUnit($domain, $key, $catalogues[$locale]->getMetadata($key, $domain));
                    $this->storage->setBaseTranslation($locale, $domain, $key , $value);
                }
            }
        }
        $this->storage->save();
        $this->log(" <info>Import success</info>\n");
    }

    protected function log($msg) {
        if ($this->logger) {
            $this->logger->write($msg);
        }
    }
}