<?php

namespace Liip\TranslationBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * A translator that can work based on the standard symfony system, or that can be
 *  switch to use the intermediate storage of the LiipTranslationBundle
 *
 * @package Liip\TranslationBundle\Translation
 */
class DualModeTranslator extends Translator
{
    /**
     * Define the current mode, could be:
     *
     *  * 'standard' :      traditional translation files (yml, xliff, etc...)
     *  * 'intermediate' :  from the new intermediate storage
     *
     * @var string
     */
    protected $mode = 'standard';
    protected static $validModes = array('standard','intermediate');

    /**
     * Store the resources added to the base class
     * @var array
     */
    protected $standardResources = array();


    /**
     * Select the current working mode
     *
     * @param $newMode
     * @throws \RuntimeException   In case of invalid mode
     */
    public function switchMode($newMode)
    {
        if (!in_array($newMode, self::$validModes)){
            throw new \RuntimeException("Invalid mode [$newMode], must be ".implode(' or ', self::$validModes));
        }
        $this->mode = $newMode;
        $this->catalogues = array(); // Clear the loaded catalogues
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
     * Override the addResource, so that the resource list is available
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
        parent::addResource($format, $resource  , $locale, $domain);
    }

    /**
     * Return the list of 'standard' resource that have been added to this translator
     *
     * @return array
     */
    public function getStandardResources()
    {
        return $this->standardResources;
    }

    /**
     * Load a resources with on of the existing loaders
     * @param $resource
     * @return mixed
     * @throws \RuntimeException
     */
    public function loadResource($resource)
    {
        foreach ($this->loaderIds as $serviceId => $formats) {
            foreach ($formats as $format) {
                if ($resource['format'] === $format) {
                    return $this->container->get($serviceId)->load($resource['path'], $resource['locale'], $resource['domain']);
                }
            }
        }
        throw new \RuntimeException("Not service found to load {$resource['path']}");
    }

}