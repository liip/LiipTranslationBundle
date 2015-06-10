<?php

namespace Liip\TranslationBundle\Form;

use Symfony\Component\Form\AbstractType;

abstract class CompatibleAbstractType extends AbstractType
{
    protected function decorateOption($options, $possibilities)
    {
        if (array_key_exists('translation_domain', $possibilities)) {
            $options['translation_domain'] = 'translation-bundle';
        }

        return $options;
    }
}
