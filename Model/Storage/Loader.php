<?php

namespace Liip\TranslationBundle\Model\Storage;

use Symfony\Component\Translation\Loader\LoaderInterface;

class Loader implements LoaderInterface
{
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Load translations from the intermediate storage
     *
     * {@inheritdoc}
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        return $this->storage->getDomainCatalogue($locale, $domain);
    }

}