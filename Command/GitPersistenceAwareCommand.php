<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\TranslationBundle\Persistence\GitPersistence;
use Symfony\Component\Process\Process;

/**
 * Initialize a Git repo inside your project to use git persistence
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Command
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
abstract class GitRepoAwareCommand extends ContainerAwareCommand
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