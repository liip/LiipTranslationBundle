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
class FileImportType extends CompatibleAbstractType
{
    public function compatibleBuildForm($builder, array $options)
    {
        $builder->add('file', 'file', $this->decorateOption(array(
            'label' => 'import.file-input.label',
            'required' => true,
            'translation_domain' => 'translation-bundle'
        ), $options));
    }

    public function getName()
    {
        return 'translation_file_import';
    }
}
