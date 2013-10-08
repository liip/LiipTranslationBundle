<?php

namespace Liip\TranslationBundle\Form;

use Symfony\Component\Form\AbstractType;

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
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
if (interface_exists('Symfony\Component\Form\FormBuilderInterface')) {
    abstract class CompatibleAbstractTypeBase extends AbstractType
    {
        /**
         * @param \Symfony\Component\Form\FormBuilderInterface|\Symfony\Component\Form\FormBuilder $builder
         * @param array $options
         */
        public function compatibleBuildForm($builder, array $options) { }

        public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
        {
            return $this->compatibleBuildForm($builder, $options);
        }
    }
} else {
    abstract class CompatibleAbstractTypeBase extends AbstractType
    {
        /**
         * @param \Symfony\Component\Form\FormBuilderInterface|\Symfony\Component\Form\FormBuilder $builder
         * @param array $options
         */
        public function compatibleBuildForm($builder, array $options) { }

        public function buildForm(\Symfony\Component\Form\FormBuilder $builder, array $options)
        {
            return $this->compatibleBuildForm($builder, $options);
        }
    }
}

abstract class CompatibleAbstractType extends CompatibleAbstractTypeBase
{
    protected function decorateOption($options, $possibilities)
    {
        if (array_key_exists('translation_domain', $possibilities)) {
            $options['translation_domain'] = 'translation-bundle';
        }
        return $options;
    }
}
