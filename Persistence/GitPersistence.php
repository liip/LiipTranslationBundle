<?php

namespace Liip\TranslationBundle\Persistence;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Persistence layer based on Yaml and Git
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
class GitPersistence extends YamlFilePersistence
{
    /**
     * @const string Command for git commit and push
     */
    const CMD_COMMITPUSH = 'git -C %s commit -a -m "%s" && git push';

    /**
     * @const string Command for git pull
     */
    const CMD_GITPULL = 'git -C %s pull';

    /**
     * @var string Class name for processes, can be a mock class instead
     */
    protected $processClass;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $options = $this->getResolvedOptions($options);

        $this->processClass = $options['processClass'];

        parent::__construct($options);
    }

    /**
     * Saves units to Yaml and auto-commits them to git
     *
     * @param array $units
     */
    public function saveUnits(array $units)
    {
        parent::saveUnits($units);

        $this->commitUnits($units);
    }

    /**
     * Deletes units and auto-commits them to git
     *
     * @param array $units
     */
    public function deleteUnits(array $units)
    {
        parent::deleteUnits($units);

        $this->commitUnits($units);
    }

    /**
     * Saves translations to Yaml and auto-commits them
     *
     * @param array $translations
     */
    public function saveTranslations(array $translations)
    {
        parent::saveTranslations($translations);

        $this->commitTranslations($translations);
    }

    /**
     * Deletes translations and auto-commits them
     *
     * @param array $translations
     */
    public function deleteTranslations(array $translations)
    {
        parent::deleteTranslations($translations);

        $this->commitTranslations($translations);
    }

    /**
     * Loads a file
     *
     * @param string $name
     *
     * @return array
     */
    protected function loadFile($name)
    {
        $this->pullTranslations();

        return parent::loadFile($name);
    }

    /**
     * Commits translations
     *
     * @param array $translations
     *
     * @return bool
     */
    protected function commitTranslations(array $translations)
    {
        $message = 'Auto-commit the following translations: ' . "\n";
        foreach ($translations as $translation) {
            $message .= ' - ' . $translation->getMessage() . "\n";
        }

        return $this->executeCmd(sprintf(
            self::CMD_COMMITPUSH,
            $this->directory,
            $message
        ));
    }

    /**
     * Commits units
     *
     * @param array $units
     *
     * @return bool
     */
    protected function commitUnits(array $units)
    {
        $message = 'Auto-commit the following Units: ' . "\n";
        foreach ($units as $unit) {
            $message .= ' - ' . $unit->getKey() . "\n";
        }

        return $this->executeCmd(sprintf(
            self::CMD_COMMITPUSH,
            $this->directory,
            $message
        ));
    }

    /**
     * Pulls the translation repository
     *
     * @return bool
     */
    protected function pullTranslations()
    {
        return $this->executeCmd(sprintf(
            self::CMD_GITPULL,
            $this->directory
        ));
    }

    /**
     * Executes a given command on the shell
     *
     * @param string $command
     *
     * @return bool
     * @throws \RuntimeException
     */
    protected function executeCmd($command)
    {
        $process = new $this->processClass($command);
        $process->run();

        if (false === $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return true;
    }

    /**
     * Get a set of usable options
     *
     * @param array $options
     *
     * @return array
     */
    protected function getResolvedOptions(array $options)
    {
        $resolver = new OptionsResolver;
        $resolver->setDefault('processClass', "Symfony\\Component\\Process\\Process")
            ->setAllowedTypes('processClass', 'string')
            ->setRequired('folder')
            ->setAllowedTypes('folder', 'string');

        return $resolver->resolve($options);
    }
}
