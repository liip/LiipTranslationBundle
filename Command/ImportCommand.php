<?php

namespace Liip\TranslationBundle\Command;

use Liip\TranslationBundle\Repository\UnitRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * Import all existing translations into the current translation storage
 * *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Command
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:import')
            ->setDescription('Import all existing translations into the application storage.')
            ->setDefinition(array(
                new InputOption('locales', null, InputOption::VALUE_REQUIRED, 'A comma separated list of locales ( --locales=en,fr,fr_CH'),
                new InputOption('override', null, InputOption::VALUE_NONE, 'override existing translations')
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Importing new translation units...');

        $importOptions = array();
        if ($input->hasOption('verbose')){
            $importOptions['logger'] = $output;
        }
        if ($locales = $input->getOption('locales')) {
            $importOptions['locale_list'] = explode(',', $locales);
        }

        if($this->getContainer()->has('security.context')) {
            $securityContext = $this->getContainer()->get('security.context');
            $securityContext->setToken(new AnonymousToken('cli', 'cli', array('ROLE_TRANSLATOR_ADMIN')));
        }

        /** @var UnitRepository $translationManager */
        $start = time();
        $importer = $this->getContainer()->get('liip.translation.symfony_importer');
        $stats = $importer->processImportOfStandardResources($importOptions, $input->getOption('override', false));
        $duration = time() - $start;

        $output->writeln(sprintf(
            "Importation done in %s[s] (%s created, %s modified and %s removed)",
            $duration, $stats['created'], $stats['updated'], $stats['deleted']
        ));
    }
}
