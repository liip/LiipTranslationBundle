<?php

namespace Liip\TranslationBundle\Form;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Form
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class TranslationType extends CompatibleAbstractType
{
    public function compatibleBuildForm($builder, array $options)
    {
        $builder->add('value', 'textarea', $this->decorateOption(array(
            'label' => 'form.translation.value',
            'required' => false
        ), $options));
    }

    public function getName()
    {
        return 'translation_translation';
    }
}
