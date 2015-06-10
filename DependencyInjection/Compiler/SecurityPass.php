<?php

namespace Liip\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to dynamically set the service for isGrant checks (can be removed when requiring Symfony 2.6+
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\DependencyInjection
 * @version 0.2.0
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @copyright Copyright (c) 2015, Liip, http://www.liip.ch
 */
class SecurityPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('liip.translation.security');
        if ($container->has('security.authorization_checker')) {
            $definition->addArgument(new Reference('security.authorization_checker'));
        } elseif ($container->has('security.context')) {
            $definition->addArgument(new Reference('security.context'));
        }
    }
}
