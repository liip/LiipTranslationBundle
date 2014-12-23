<?php

namespace Liip\TranslationBundle\Filter;

use Liip\TranslationBundle\Repository\UnitRepository;
use Liip\TranslationBundle\DependencyInjection\Configuration;

/**
 * Allow to filter translation based on a set of session filters
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Form
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class FilterManager
{
    /** @var array $config The injected config */
    protected $config;

    /** @var \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session $session */
    protected $session;

    /** @var UnitRepository $repository **/
    protected $repository;

    /**
     * @param array $config
     * @param \Symfony\Component\HttpFoundation\Session|\Symfony\Component\HttpFoundation\Session\Session $session
     * @param UnitRepository $repository
     */
    public function __construct($config, $session, UnitRepository $repository)
    {
        $this->config = $config;
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * Return the current filters to apply (from the session or the default config)
     *
     * @return array
     */
    public function getCurrentFilters()
    {
        $sessionValues = $this->session->get(Configuration::SESSION_PREFIX . 'filters', null);

        return $sessionValues !== null ? $sessionValues : $this->getDefaultFilters();
    }

    /**
     * Update the filters in session
     *
     * @param $newFilters
     */
    public function updateFilters($newFilters)
    {
        $this->session->set(Configuration::SESSION_PREFIX.'filters', $newFilters);
    }

    /**
     * Clear the current filters
     */
    public function resetFilters()
    {
        $this->session->set(Configuration::SESSION_PREFIX.'filters', null);
    }

    /**
     * Get the default filters from the config
     *
     * @return array
     */
    public function getDefaultFilters()
    {
        return isset($this->config['interface']['default_filters']) ? $this->config['interface']['default_filters'] : array();
    }

}
