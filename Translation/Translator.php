<?php

namespace Liip\TranslationBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * A translator that work over an intermediate storage
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Storage\Persistence
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Translator extends BaseTranslator
{
    /**
     * Store all the classical resources to be able to import them in the storage
     * @var array
     */
    protected $standardResources = array();

    /**
     * Override the addResource, so that we can keep tracking standard resources
     *  but we don't call the parent method as we don't want to use them anymore
     * @param string $format
     * @param mixed $resource
     * @param string $locale
     * @param null $domain
     */
    public function addResource($format, $resource, $locale, $domain = null)
    {
        $this->standardResources[] = array(
            'format'=>$format,
            'path'=>$resource,
            'locale'=>$locale,
            'domain'=> $domain === null ? 'messages' : $domain
        );
    }

    /**
     * Return the list of 'standard' resources
     * @return array
     */
    public function getStandardResources()
    {
        // Check validity
        foreach ($this->standardResources as $resource) {
            if (!file_exists($resource['path'])) {
                throw new \RuntimeException('Ressources list is outdated, please run a cache:clear to update it');
            }
        }
        return $this->standardResources;
    }


    /**
     * Return the full catalog of a given locale
     *
     * @param $locale
     * @return \Symfony\Component\Translation\MessageCatalogueInterface
     */
    public function getCatalogue($locale)
    {
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }


    /**
     * Load a specific resource
     * @param $resource
     * @return MessageCatalogue
     * @throws \RuntimeException
     */
    public function loadResource($resource)
    {
        // If possible use our custom xliff loader, so we get metadata
        if (in_array($resource['format'], array('xliff', 'xlf'))) {
            return $this->container->get('liip.xliff.loader')->load($resource['path'], $resource['locale'], $resource['domain']);
        }

        // Search for an other services
        foreach ($this->loaderIds as $serviceId => $formats) {
            if(! is_array($formats)) {
                $formats = array($formats);
            }
            foreach ($formats as $format) {
                if ($resource['format'] === $format) {
                    return $this->container->get($serviceId)->load($resource['path'], $resource['locale'], $resource['domain']);
                }
            }
        }

        throw new \RuntimeException("Not service found to load {$resource['path']}");
    }


    /**
     * Initialize the translation before loading catalogues from the storage
     */
    protected function initialize()
    {
        // Register our custom loader
        $this->addLoader('liip', $this->container->get('liip.translation.loader'));

        // Register all catalogues we have in the storage
        foreach ($this->container->get('liip.translation.storage')->getTranslations() as $domain => $keys) {
            foreach ($keys as $key => $locales) {
                foreach($locales as $locale => $value) {
                    parent::addResource('liip', 'intermediate.storage', $locale, $domain);
                }
            }
        }
    }

    public function clearCacheForLocale($locale)
    {
        $cacheFile = $this->options['cache_dir'].'/catalogue.'.$locale.'.php';
        if (is_file($cacheFile)) {
            unlink($cacheFile);
        }
    }

}