<?php

namespace Liip\TranslationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Liip\TranslationBundle\DependencyInjection\Compiler\SecurityPass;
use Liip\TranslationBundle\DependencyInjection\Compiler\UpdateRoleHierarchyPass;
use Liip\TranslationBundle\DependencyInjection\Compiler\TranslatorPass;

/**
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class LiipTranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SecurityPass());
        $container->addCompilerPass(new UpdateRoleHierarchyPass());
        $container->addCompilerPass(new TranslatorPass());
    }
}
