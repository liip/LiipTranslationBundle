<?php

namespace Liip\TranslationBundle\Import;

use Liip\TranslationBundle\DependencyInjection\Configuration;
use Liip\TranslationBundle\Persistence\PersistenceInterface;
use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * To be completed
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
class FileImporter
{
    /** @var UnitRepository $repository */
    protected $repository;
    /** @var \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session $session */
    protected $session;
    /** @var Translator $translator */
    protected $translator;

    /**
     * @param UnitRepository $repository
     * @param \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session $session
     * @param Translator $translator
     */
    public function __construct(UnitRepository $repository, $session, Translator $translator)
    {
        $this->repository = $repository;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * Return the UploadedFile original extension. This is here for
     * compatibility reason with Symfony 2.0.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getFileExtension(UploadedFile $file)
    {
        if(method_exists($file, 'getClientOriginalExtension')) {
            return $file->getClientOriginalExtension();
        } else {
            return pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        }
    }

    /**
     * Take care of uploaded files (including zip) for importing resources
     *
     * @param UploadedFile $file
     */
    public function handleUploadedFile(UploadedFile $file)
    {
        if ($this->getFileExtension($file) === 'zip') {
            $tempFolder = sys_get_temp_dir().md5(time());
            mkdir($tempFolder);
            $zip = new \ZipArchive;
            $zip->open($file->getRealPath());
            $zip->extractTo($tempFolder);
            $zip->close();
            foreach(scandir($tempFolder) as $path) {
                if (is_file($tempFolder.'/'.$path)) {
                    $this->import($tempFolder.'/'.$path);
                }
            }
            unset($tempFolder);
        }
        else {
            $this->import($file->getRealPath(), $file->getClientOriginalName());
        }
    }

    /**
     * Add a file to the current import buffer
     *
     * @param string $filePath   The path to the file
     * @param string $fileName   Optional, the filename to parse to extract resources data
     * @throws \RuntimeException
     */
    protected function import($filePath, $fileName = null)
    {
        // Filename parsing
        if ($fileName == null){
            $fileName = basename($filePath);
        }
        if (!preg_match('/\w+\.\w+\.\w+/', $fileName)){
            throw new \RuntimeException("Invalid filename [$fileName], all translation files must be named: domain.locale.format (ex: messages.en.yml)");
        }
        list($domain, $locale, $format) = explode('.', $fileName, 3);
        $catalogue = $this->translator->loadResource(array(
            'format' => $format,
            'locale' => $locale,
            'domain' => $domain,
            'path' => $filePath
        ));

        // Merge with existing entries
        $translations = $this->getCurrentTranslations();
        if (!array_key_exists($locale, $translations)){
            $translations[$locale] = array('new' => array(), 'updated' => array());
        }
        foreach($catalogue->all() as $domain => $messages) {
            foreach ($messages as $key => $value) {
                if ($trans = $this->repository->findTranslation($domain, $key, $locale)) {
                    if ($trans !== $value) {
                        $translations[$locale]['updated'][$domain][$key] = array('old' => $trans, 'new' => $value);
                    }
                } else {
                    $translations[$locale]['new'][$domain][$key] = $value;
                }
            }
        }

        $this->session->set(Configuration::SESSION_PREFIX.'import-list', $translations);
    }

    /**
     * Return the current buffer
     *
     * @return array
     */
    public function getCurrentTranslations()
    {
        return $this->session->get(Configuration::SESSION_PREFIX.'import-list', array());
    }

    public function remove($domain, $key, $locale)
    {
        $translations = $this->getCurrentTranslations();
        unset($translations[$locale]['new'][$domain][$key]);
        unset($translations[$locale]['updated'][$domain][$key]);
        $this->session->set(Configuration::SESSION_PREFIX.'import-list', $translations);
    }

    public function persists(PersistenceInterface $persistence, $locale = null)
    {
        $translations = $this->getCurrentTranslations();

        $units = array();
        if ($locale == 'all') {
            foreach($translations as $locale => $data) {
                $this->persists($persistence, $locale);
            }
            return;
        }

        foreach ($translations[$locale]['new'] as $domain => $newTranslations) {
            foreach($newTranslations as $key => $value) {
                $this->repository->findByDomainAndTranslationKey($domain, $key)->setTranslation($locale, $value);
            }
        }

        foreach ($translations[$locale]['updated'] as $domain => $newTranslations) {
            foreach($newTranslations as $key => $newValue) {
                $this->repository->findTranslation($domain, $key, $locale)->setValue($newValue);
            }
        }

        unset($translations[$locale]);
        $this->session->set(Configuration::SESSION_PREFIX.'import-list', $translations);

        $persistence->saveUnits($units);
    }

    /**
     * Reset the buffer
     */
    public function clear()
    {
        $this->session->set(Configuration::SESSION_PREFIX.'import-list', null);
    }
}