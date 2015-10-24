<?php

namespace Liip\TranslationBundle\Import;

use Liip\TranslationBundle\DependencyInjection\Configuration;
use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Importer session based, allow to upload several files, then to edit/remove translations before processing the import.
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class SessionImporter
{
    /** @var UnitRepository $repository */
    protected $repository;

    /** @var \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session $session */
    protected $session;

    /** @var Translator $translator */
    protected $translator;

    /**
     * @param UnitRepository                                                                              $repository
     * @param \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session $session
     * @param Translator                                                                                  $translator
     */
    public function __construct(UnitRepository $repository, $session, Translator $translator)
    {
        $this->repository = $repository;
        $this->session = $session;
        $this->translator = $translator;
    }

    public function getCurrentTranslations()
    {
        return $this->getTranslationsFromSession();
    }

    /**
     * Take care of uploaded files (including zip) for importing resources.
     *
     * @param UploadedFile $file
     *
     * @return array
     */
    public function handleUploadedFile(UploadedFile $file)
    {
        if ($file->getClientOriginalExtension() === 'zip') {
            $tempFolder = $this->extractZip($file);
            $counters = array('new' => 0, 'updated' => 0);
            foreach (scandir($tempFolder) as $path) {
                if (is_file($tempFolder.'/'.$path)) {
                    $fileCounters = $this->importFile($tempFolder.'/'.$path);
                    $counters['new'] += $fileCounters['new'];
                    $counters['updated'] += $fileCounters['updated'];
                }
            }
            unset($tempFolder);
        } else {
            $counters = $this->importFile($file->getRealPath(), $file->getClientOriginalName());
        }

        return $counters;
    }

    /**
     * Extract a zip file into a temp folder and return the folder path.
     *
     * @param UploadedFile $file
     *
     * @return string The path to the temp folder
     *
     * @throws ImportException
     */
    protected function extractZip($file)
    {
        $tempFolder = sys_get_temp_dir().'/'.md5(rand(0, 99999));
        if (@mkdir($tempFolder) === false) {
            throw new ImportException('Impossible to create a temp folder for zip extraction');
        }
        $zip = new \ZipArchive();
        $zip->open($file->getRealPath());
        $zip->extractTo($tempFolder);
        $zip->close();

        return $tempFolder;
    }

    /**
     * Add a file to the current import buffer.
     *
     * @param string $filePath The path to the file
     * @param string $fileName Optional, the filename to parse to extract resources data
     *
     * @return array
     *
     * @throws ImportException
     */
    protected function importFile($filePath, $fileName = null)
    {
        // Filename parsing
        if ($fileName == null) {
            $fileName = basename($filePath);
        }
        if (!preg_match('/\w+\.\w+\.\w+/', $fileName)) {
            throw new ImportException("Invalid filename [$fileName], all translation files must be named: domain.locale.format (ex: messages.en.yml)");
        }
        list($domain, $locale, $format) = explode('.', $fileName, 3);
        $catalogue = $this->translator->loadResource(array(
            'format' => $format,
            'locale' => $locale,
            'domain' => $domain,
            'path' => $filePath,
        ));

        // Merge with existing entries
        $translations = $this->getTranslationsFromSession();
        $counters = array('new' => 0, 'updated' => 0);
        if (!array_key_exists($locale, $translations)) {
            $translations[$locale] = array('new' => array(), 'updated' => array());
        }
        foreach ($catalogue->all() as $domain => $messages) {
            foreach ($messages as $key => $value) {
                if ($trans = $this->repository->findTranslation($domain, $key, $locale, true)) {
                    if ($trans->getValue() !== $value) {
                        $translations[$locale]['updated'][$domain][$key] = array('old' => $trans->getValue(), 'new' => $value);
                        $counters['updated'] += 1;
                    }
                } else {
                    $translations[$locale]['new'][$domain][$key] = $value;
                    $counters['new'] += 1;
                }
            }
        }

        $this->updateSession($translations);

        return $counters;
    }

    public function remove($domain, $key, $locale)
    {
        $translations = $this->getTranslationsFromSession();
        unset($translations[$locale]['new'][$domain][$key]);
        unset($translations[$locale]['updated'][$domain][$key]);
        $this->updateSession($translations);
    }

    public function edit($domain, $key, $locale, $newValue)
    {
        throw new \Exception('Implement me');
    }

    public function comfirmImportation($locale = null)
    {
        // Persisting locale [all], means to persist all locale separatly
        $locales = array($locale);
        if ($locale == 'all') {
            $locales = array_keys($this->getTranslationsFromSession());
        }

        // Import
        foreach ($locales as $locale) {
            $this->doImport($locale);
        }

        // Save and clear cache
        $stat = $this->repository->persist();
        $this->translator->clearCache();

        return $stat;
    }

    protected function doImport($locale)
    {
        $translations = $this->getTranslationsFromSession();
        $existingUnits = $this->repository->getAllByDomainAndKey();

        // Add new translations, create the unit if require
        foreach ($translations[$locale]['new'] as $domain => $newTranslations) {
            foreach ($newTranslations as $key => $value) {
                if (!isset($existingUnits[$domain][$key])) {
                    $existingUnits[$domain][$key] = $this->repository->createUnit($domain, $key, array('created-at-import' => true));
                }
                $existingUnits[$domain][$key]->setTranslation($locale, $value);
            }
        }

        // Update existing translations
        foreach ($translations[$locale]['updated'] as $domain => $newTranslations) {
            foreach ($newTranslations as $key => $data) {
                $existingUnits[$domain][$key]->getTranslation($locale)->setValue($data['new']);
            }
        }

        // Remove the processed locale from the session
        unset($translations[$locale]);
        $this->updateSession($translations);
    }

    /**
     * Reset the buffer.
     */
    public function clear()
    {
        $this->session->set(Configuration::SESSION_PREFIX.'import-list', array());
    }

    protected function getTranslationsFromSession()
    {
        return $this->session->get(Configuration::SESSION_PREFIX.'import-list', array());
    }

    protected function updateSession($translations)
    {
        foreach ($translations as $locale => $values) {
            // Clear empty domains
            foreach (array('new', 'updated') as $modififactionType) {
                foreach ($values[$modififactionType] as $domain => $trads) {
                    if (count($trads) == 0) {
                        unset($translations[$locale][$modififactionType][$domain]);
                    }
                }
            }

            // Clear empty locales
            if (count($translations[$locale]['new']) == 0 && count($translations[$locale]['updated']) == 0) {
                unset($translations[$locale]);
            }
        }

        $this->session->set(Configuration::SESSION_PREFIX.'import-list', $translations);
    }
}
