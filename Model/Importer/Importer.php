<?php

namespace Liip\TranslationBundle\Model\Importer;

use Liip\TranslationBundle\Storage\Storage;
use Liip\TranslationBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * To be completed
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
class Importer {

    protected $session;
    protected $translator;

    public function __construct($session, Storage $storage, Translator $translator)
    {
        $this->session = $session;
        $this->storage = $storage;
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
                    $this->addFile($tempFolder.'/'.$path);
                }
            }
            unset($tempFolder);
        }
        else {
            $this->addFile($file->getRealPath(), $file->getClientOriginalName());
        }
    }

    /**
     * Add a file to the current import buffer
     *
     * @param string $filePath   The path to the file
     * @param string $fileName   Optional, the filename to parse to extract resources data
     */
    public function addFile($filePath, $fileName = null)
    {
        // File parsing
        if ($fileName == null){
            $fileName = basename($filePath);
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
                if (($trans = $this->storage->getTranslation($locale, $domain, $key)) === null) {
                    $translations[$locale]['new'][$domain][$key] = $value;
                }
                else {
                    if ($trans !== $value) {
                        $translations[$locale]['updated'][$domain][$key] = array('old'=>$trans, 'new'=>$value);
                    }
                }
            }
        }

        $this->session->set('import-list', $translations);
    }

    /**
     * Return the current buffer
     *
     * @return array
     */
    public function getCurrentTranslations()
    {
        return $this->session->get('import-list', array());
    }

    /**
     * Remove an entry from the buffer
     *
     * @param $locale
     * @param $domain
     * @param $key
     */
    public function removeEntry($locale, $domain, $key)
    {
        $translations = $this->getCurrentTranslations();
        unset($translations[$locale]['new'][$domain][$key]);
        unset($translations[$locale]['updated'][$domain][$key]);
        $this->session->set('import-list', $translations);
    }

    /**
     * Process the import and remove the translationsfrom the buffer
     *
     * @param $locale
     */
    public function processImport($locale)
    {
        $translations = $this->getCurrentTranslations();

        if ($locale == 'all') {
            foreach($translations as $locale => $data) {
                $this->processImport($locale);
            }
            return;
        }

        foreach ($translations[$locale]['new'] as $domain => $newTranslations) {
            foreach($newTranslations as $key => $value) {
                $this->storage->addNewTranslation($locale, $domain, $key, $value);
            }
        }

        foreach ($translations[$locale]['updated'] as $domain => $newTranslations) {
            foreach($newTranslations as $key => $newValue) {
                $this->storage->updateTranslation($locale, $domain, $key, $newValue);
            }
        }

        unset($translations[$locale]);
        $this->session->set('import-list', $translations);

        $this->storage->save();
    }

    /**
     * Reset the buffer
     */
    public function reset()
    {
        $this->session->set('import-list', null);
    }
}