<?php
/**
 * User: dj
 * Date: 19.09.13
 */

namespace Liip\TranslationBundle\Form;

use Symfony\Component\Form\AbstractType;

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

