<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Liip\TranslationBundle\Model\Manager;
use Symfony\Component\Translation\Translator;

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
            ->setDescription('Import all existing translations into the application storage')
            ->setDefinition(array(
                new InputOption('locales', null, InputOption::VALUE_REQUIRED, 'A comma separated list of locales ( --locales=en,fr,fr_CH')
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importOptions = array();
        $importOptions['logger'] = $output;
        if ($locales = $input->getOption('locales')) {
            $importOptions['locale_list'] = explode(',', $locales);
        }

        /** @var Manager $translationManager */
        $translationManager =  $this->getContainer()->get('liip.translation.manager');
        $translationManager->processImportOfStandardResources($importOptions);

        $translationManager->clearSymfonyCache();
    }

}