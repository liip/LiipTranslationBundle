<?php
/**
 * User: dj
 * Date: 13.09.13
 */

namespace Liip\TranslationBundle\Model\Storage;


use Liip\TranslationBundle\Model\Storage\Repository\FileRepository;
use Liip\TranslationBundle\Model\Storage\Repository\RepositoryInterface;

class Storage {

    /** @var  RepositoryInterface */
    protected $repository;
    protected $units;
    protected $translations;

    public function load() {
        $this->repository = new FileRepository();
        $this->repository->load();
        $this->units = $this->repository->getUnits();
        $this->translations = $this->repository->getTranslations();
    }

    /**
     * Save the current unit and translation into the repository
     */
    public function save() {
        $this->repository->setTranslations($this->translations);
        $this->repository->setUnits($this->units);
        $this->repository->save();
    }

    /**
     * Create or update a translation unit
     * @param $domain
     * @param $key
     * @param $metadata
     */
    public function createOrUpdateTranslationUnit($domain, $key, $metadata)
    {
        if (!array_key_exists($domain, $this->units)) {
            $this->units[$domain] = array();
        }
        $this->units[$domain][$key] = $metadata;
    }

    /**
     * Set a translation value, only if it's currently empty
     * @param $locale
     * @param $domain
     * @param $key
     * @param $value
     */
    public function setBaseTranslation($locale, $domain, $key , $value)
    {
        if (!array_key_exists($locale, $this->translations)) {
            $this->translations[$locale] = array();
        }
        if (!array_key_exists($domain, $this->translations[$locale])) {
            $this->translations[$locale][$domain] = array();
        }
        if (!array_key_exists($key, $this->translations[$locale][$domain])) {
            $this->translations[$locale][$domain][$key] = $value;
        }
    }

}