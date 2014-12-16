<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
class GitPersistenceInitCommand extends GitPersistenceAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('translation:git-persistence:init')
            ->setDescription('Initializes a Git repository at the configured folder where your translations will be stored.')
            ->setDefinition(array(
                new InputOption('remote', null, InputOption::VALUE_OPTIONAL, 'Git remote to clone and pull from and to push to (if given, command omits asking any questions)'),
            ))
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $askQuestions = true;
        $remote = $input->getOption('remote');
        if (null !== $remote) {
            $askQuestions = false;
        }

        $persistence = $this->getPersistence();

        $dir = rtrim($persistence->getDirectoryName(), DIRECTORY_SEPARATOR);

        if ($askQuestions) {
            $dialog = $this->getHelper('dialog');

            $remote = $dialog->ask($output, 'Please enter a Git remote to clone and pull from and to push to: ');

            if (!$dialog->askConfirmation($output, '<question>' . $remote . ' will be cloned into ' . $dir . '. Continue? [y] </question>', false)) {
                return;
            }
        }

        if (false === is_dir($dir)) {
            $output->writeln('Creating directory "' . $dir . '"...');
            $directoryProcess = new Process('mkdir -p ' . $dir);
            $directoryProcess->run();
            if (false === $directoryProcess->isSuccessful()) {
                throw new \RuntimeException($directoryProcess->getErrorOutput());
            }
        } else {
            $output->writeln('Directory "' . $dir . '" already exists, not creating.');
            if (is_dir($dir . DIRECTORY_SEPARATOR . '.git')) {
                throw new \RuntimeException('"' . $dir . '" already is a git repository.');
            }
        }

        $output->writeln('Cloning...');
        $gitProcess = new Process(sprintf(
            'git clone %s %s',
            $remote,
            $dir
        ));

        $gitProcess->run();

        if (false === $gitProcess->isSuccessful()) {
            throw new \RuntimeException($gitProcess->getErrorOutput());
        }

        $output->writeln(sprintf('Init of git repository for remote "%s" in %s successful', $remote, $dir));
    }
}
