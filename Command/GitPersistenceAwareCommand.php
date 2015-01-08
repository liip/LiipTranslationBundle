<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Liip\TranslationBundle\Persistence\GitPersistence;

/**
 * Abstract command class for every command that is related to GitPersistence
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Command
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Pascal Thormeier <pascal.thormeier@liip.ch>
 * @copyright Copyright (c) 2014, Liip, http://www.liip.ch
 */
abstract class GitPersistenceAwareCommand extends ContainerAwareCommand
{
    /**
     * Returns the configured repository, which should be a GitPersistence
     *
     * @return GitPersistence
     *
     * @throws \RuntimeException
     */
    public function getPersistence()
    {
        $persistence = $this->getContainer()->get('liip.translation.persistence');

        if (false === $persistence instanceof GitPersistence) {
            throw new \RuntimeException(sprintf(
                'Cannot initialize git repository, configured persistence should be "%s", "%s" given',
                'Liip\TranslationBundle\Persistence\GitPersistence',
                get_class($persistence)
            ));
        }

        return $persistence;
    }
}
