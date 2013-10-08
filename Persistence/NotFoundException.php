<?php

namespace Liip\TranslationBundle\Persistence;

/**
 * Exception used when a requested unit doesn't exist in the storage
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Persistence
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class NotFoundException extends \Exception {

    public function __construct($domain, $key)
    {
        parent::__construct("No translation unit found for domain [$domain] and key [$key]");
    }

}