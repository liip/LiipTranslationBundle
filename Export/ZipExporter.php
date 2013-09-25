<?php

namespace Liip\TranslationBundle\Export;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Symfony\Component\Yaml\Yaml;

/**
 * Export a set of Unit into YML files grouped into a ZIP file
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Import
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class ZipExporter {

    protected $unitsByLocaleAndDomain;

    /**
     * Set the units you want to export
     *
     * @param Unit[] $units
     */
    public function setUnits($units)
    {
        foreach($units as $unit) {
            foreach($unit->getTranslations() as $translation) {
                $this->unitsByLocaleAndDomain[$translation->getLocale()][$translation->getDomain()][] = $translation;
            }
        }
    }

    /**
     * Set the units you want to export
     *
     * @return string The zip file content
     */
    public function createZipContent()
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'temp-zip-');
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE);
        $this->addYmlFiles($zip);
        $zip->close();
        $content = file_get_contents($zipFile);
        unlink($zipFile);
        return $content;
    }

    protected function addYmlFiles(\ZipArchive $zip)
    {
        foreach ($this->unitsByLocaleAndDomain as $locale => $translationDomains) {
            foreach ($translationDomains as $domain => $translations) {
                $zip->addFromString("$domain.$locale.yml", $this->createYml($translations));
            }
        }
    }

    /**
     * @param Translation[] $translations
     * @return string
     */
    protected function createYml($translations) {
        $flatArray = array();
        foreach($translations as $translation) {
            $flatArray[$translation->getKey()] = $translation->getValue();
        }

        return Yaml::dump($flatArray);
    }
}