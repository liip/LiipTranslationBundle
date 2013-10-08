<?php

namespace Liip\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that inject the security.context to our own security manager
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\DependencyInjection
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class EnableSecurityPass implements CompilerPassInterface
{
    /**
    * {@inheritDoc}
    */
    public function process(ContainerBuilder $container)
    {
        if($container->hasDefinition('security.context')) {
            $securityDefinition = $container->getDefinition('liip.translation.security');
            $securityDefinition->addMethodCall(
                'setSecurityContext',
                array(new Reference('security.context'))
            );
        }
    }
}
