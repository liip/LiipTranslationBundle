<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List all existing translations resources
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
class ListResourcesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:list-resources')
            ->setDescription('List all existing translations resources found in the application.')
            ->setDefinition(array(
                new InputOption('group', 'g', InputOption::VALUE_NONE, 'Display a single entry for all languages'),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\n<comment>List of available translation resources:</comment>\n");
        $resources = $this->getContainer()->get('liip.translation.symfony_importer')->getStandardResources();

        if (! $input->getOption('group')) {
            foreach ($resources as $resource) {
                $path = $resource['path'];
                $output->writeln("\t".realpath($path));
            }
        } else {
            $languageByResources = array();
            foreach ($resources as $resource) {
                $pathParts = pathinfo(realpath($resource['path']));
                list($domain, $locale) = explode('.', $pathParts['filename']);
                $resource = $pathParts['dirname'].'/'.$domain;
                if (! array_key_exists($resource, $languageByResources)) {
                    $languageByResources[$resource] = array();
                }
                $languageByResources[$resource][] = $locale;
            }

            foreach ($languageByResources as $resource => $langauges) {
                $output->writeln("\t<info>$resource</info>");
                $output->writeln("\t\tavailable in [".implode(', ', $langauges)."]\n");
            }
        }
    }
}
