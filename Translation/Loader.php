<?php

namespace Liip\TranslationBundle\Translation;

use Liip\TranslationBundle\Storage\Storage;
use Symfony\Component\Translation\Loader\LoaderInterface;

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