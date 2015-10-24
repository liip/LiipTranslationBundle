<?php

namespace Liip\TranslationBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * To be completed.
 *
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
class TranslationType extends CompatibleAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', 'textarea', $this->decorateOption(array(
            'label' => 'form.translation.value',
            'required' => false,
        ), $options));
    }

    public function getName()
    {
        return 'translation_translation';
    }
}
