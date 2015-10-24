<?php

namespace Liip\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to update the liip.translation.translator.
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @version 0.1.0
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @copyright Copyright (c) 2015, Liip, http://www.liip.ch
 */
class TranslatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('translator', 'liip.translation.translator');
    }
}
