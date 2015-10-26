<?php

namespace Liip\TranslationBundle\Persistence;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Persistence layer based on Yaml and Git.
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Pascal Thormeier <pascal.thormeier@liip.ch>
 * @copyright Copyright (c) 2014, Liip, http://www.liip.ch
 */
class GitPersistence extends YamlFilePersistence
{
    /**
     * @const string Command for git commit and push
     */
    const CMD_COMMITPUSH = '(cd %s && git add . && git commit -a -m "%s" && git push --all origin)';

    /**
     * @const string Command for git pull
     */
    const CMD_GITPULL = '(cd %s && git pull)';

    /**
     * @const strimg Command for cloning
     */
    const CMD_GITCLONE = 'git clone %s %s';

    /**
     * @var string Class name for processes, can be a mock class instead
     */
    protected $processClass;

    /**
     * Constructor.
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
     * Saves units to Yaml and auto-commits them to git.
     *
     * @param array $units
     */
    public function saveUnits(array $units)
    {
        parent::saveUnits($units);

        $this->commitUnits($units);
    }

    /**
     * Deletes units and auto-commits them to git.
     *
     * @param array $units
     */
    public function deleteUnits(array $units)
    {
        parent::deleteUnits($units);

        $this->commitUnits($units);
    }

    /**
     * Saves translations to Yaml and auto-commits them.
     *
     * @param array $translations
     */
    public function saveTranslations(array $translations)
    {
        parent::saveTranslations($translations);

        $this->commitTranslations($translations);
    }

    /**
     * Deletes translations and auto-commits them.
     *
     * @param array $translations
     */
    public function deleteTranslations(array $translations)
    {
        parent::deleteTranslations($translations);

        $this->commitTranslations($translations);
    }

    /**
     * Returns the directory given.
     *
     * @return string
     */
    public function getDirectoryName()
    {
        return $this->directory;
    }

    /**
     * Pulls the translation repository.
     *
     * @return bool
     */
    public function pullTranslations()
    {
        return $this->executeCmd(sprintf(
            self::CMD_GITPULL,
            $this->directory
        ));
    }

    /**
     * Clones a given remote.
     *
     * @param string $remote
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function cloneRepository($remote)
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->directory);

        if ($fileSystem->exists($this->directory.DIRECTORY_SEPARATOR.'.git')) {
            throw new \RuntimeException(sprintf('"%s" already is a git repository.', $this->directory));
        }

        return $this->executeCmd(sprintf(
            self::CMD_GITCLONE,
            $remote,
            $this->directory
        ));
    }

    /**
     * Commits translations.
     *
     * @param array $translations
     *
     * @return bool
     */
    protected function commitTranslations(array $translations)
    {
        return $this->executeCmd(sprintf(
            self::CMD_COMMITPUSH,
            $this->directory,
            'Auto-commit translations'
        ));
    }

    /**
     * Commits units.
     *
     * @param array $units
     *
     * @return bool
     */
    protected function commitUnits(array $units)
    {
        return $this->executeCmd(sprintf(
            self::CMD_COMMITPUSH,
            $this->directory,
            'Auto-commit units'
        ));
    }

    /**
     * Executes a given command on the shell.
     *
     * @param string $command
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    protected function executeCmd($command)
    {
        $process = new $this->processClass($command);
        $process->run();

        if (false === $process->isSuccessful() && strlen($process->getErrorOutput()) > 0) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Get a set of usable options.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getResolvedOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
                'processClass' => 'Symfony\\Component\\Process\\Process',
            ))
            ->setRequired(array('folder'))
            ->setAllowedTypes(array(
                'processClass' => 'string',
                'folder' => 'string',
            ))
        ;

        return $resolver->resolve($options);
    }
}
