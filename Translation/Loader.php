<?php

namespace Liip\TranslationBundle\Translation;

use Liip\TranslationBundle\Repository\UnitRepository;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * Load translations from the repository (intermediate storage)
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Translation
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class Loader implements LoaderInterface
{
    private $repository;

    public function __construct(UnitRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        return $this->repository->getMessageCatalogues($locale, $domain);
    }
}
