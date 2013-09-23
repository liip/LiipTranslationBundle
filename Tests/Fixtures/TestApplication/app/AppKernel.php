<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Liip\TranslationBundle\LiipTranslationBundle(),
            new Liip\TranslationBundle\Tests\Fixtures\TestApplication\TestBundle\TestBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return __DIR__.'/cache';
    }

    public function getLogDir()
    {
        return __DIR__.'/logs';
    }
}
