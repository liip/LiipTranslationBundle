<?php

namespace Liip\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
* TODO
*/
class EnableSecurityPass implements CompilerPassInterface
{
    /**
* {@inheritDoc}
*/
    public function process(ContainerBuilder $container)
    {
        // Inject the PartialCountriesData dynamically into our custom Router class
        if($container->hasDefinition('security.context')) {
            $securityDefinition = $container->getDefinition('liip.translation.security');
            $securityDefinition->addMethodCall(
                'setSecurityContext',
                array(new Reference('security.context'))
            );
        }
    }
}
