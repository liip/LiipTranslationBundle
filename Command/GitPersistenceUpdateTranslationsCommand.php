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
class GitPersistenceUpdateTranslationsCommand extends GitPersistenceAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('translation:git-persistence:pull')
            ->setDescription('Updates the configured Git repository at the configured folder.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $persistence = $this->getPersistence();
        $output->writeln(sprintf('Pulling translations in %s', $persistence->getDirectoryName()));

        $gitOutput = $persistence->pullTranslations();
        $output->writeln($gitOutput);

        $output->writeln('Done.');
    }
}