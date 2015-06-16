<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
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
 * @author Pascal Thormeier <pascal.thormeier@liip.ch>
 * @copyright Copyright (c) 2014, Liip, http://www.liip.ch
 */
class GitPersistenceInitCommand extends GitPersistenceAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('liip:translation:git-persistence:init')
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
        $remote = $input->getOption('remote');

        $persistence = $this->getPersistence();

        if (null === $remote) {
            $dialog = $this->getHelper('dialog');

            $remote = $dialog->ask($output, 'Please enter a Git remote to clone and pull from and to push to: ');

            if (!$dialog->askConfirmation($output, '<question>' . $remote . ' will be cloned into ' . $persistence->getDirectoryName() . '. Continue? [y] </question>', false)) {
                $output->writeln('Not cloning, cancelled.');
                return;
            }
        }

        $output->writeln('Cloning...');
        $persistence->cloneRepository($remote);

        $output->writeln(sprintf('Init of git repository for remote "%s" in %s successful', $remote, $persistence->getDirectoryName()));
    }
}
