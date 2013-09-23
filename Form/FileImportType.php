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
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
if(interface_exists('Symfony\Component\Form\FormBuilderInterface')) {
    class FileImportType extends AbstractType
    {
        public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('file', 'file', array(
                    'label' => 'import.file-input.label',
                    'translation_domain' => 'translation-bundle',
                    'required' => true
                ))
            ;
        }

        public function getName()
        {
            return 'translation_file_import';
        }
    }
} else {
    class FileImportType extends AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder
            ->add('file', 'file', array(
                'label' => 'import.file-input.label',
                'required' => true
            ))
        ;
    }

    public function getName()
    {
        return 'translation_file_import';
    }
}
}

