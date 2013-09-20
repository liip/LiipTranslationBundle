<?php
/**
 * User: dj
 * Date: 19.09.13
 */

namespace Liip\TranslationBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FileImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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