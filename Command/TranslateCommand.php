<?php

namespace Liip\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Basic command that just translate a unit.
 * *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Command
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class TranslateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('translation:translate')
            ->setDescription('Translation a key (useful for testing)')
            ->setDefinition(array(
                new InputArgument('key', InputArgument::REQUIRED, 'The key to translate'),
                new InputOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Destination locale', 'en'),
                new InputOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Message domain', 'messages'),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->getContainer()->get('translator');
        $key = $input->getArgument('key');
        $domain = $input->getOption('domain');
        $locale = $input->getOption('locale');
        $output->writeln(
            "Translation in [<comment>$locale</comment>] for key [<comment>$key</comment>] in domain [<comment>$domain</comment>] is: ".
            '<info>'.$translator->trans($key, array(), $domain, $locale).'</info>'
        );
    }
}
