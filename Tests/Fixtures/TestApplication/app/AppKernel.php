<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

umask(0000);

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Liip\TranslationBundle\LiipTranslationBundle(),
            new Liip\TranslationBundle\Tests\Fixtures\TestApplication\TestBundle\TestBundle()
        );

        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/LiipTranslationBundleTest/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/LiipTranslationBundleTest/logs';
    }
}
