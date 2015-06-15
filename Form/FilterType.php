<?php

namespace Liip\TranslationBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form to filter the translation list
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Form
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Crettenand <gilles.crettenand@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class FilterType extends CompatibleAbstractType
{
    private $locales;
    private $domains;

    public function __construct(array $locales, array $domains)
    {
        $this->locales = count($locales) > 0 ? array_combine($locales, $locales) : array();
        $this->domains = count($domains) > 0 ? array_combine($domains, $domains) : array();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('empty', 'checkbox', $this->decorateOption(array(
                'required' => false,
                'label' => 'form.filter.empty',
            ), $options))
            ->add('domain', 'choice', $this->decorateOption(array(
                'choices' => $this->domains,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'form.filter.domain',
            ), $options))
            ->add('languages', 'choice', $this->decorateOption(array(
                'choices' => $this->locales,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'form.filter.locale',
            ), $options))
            ->add('key', 'text', $this->decorateOption(array(
                'required' => false,
                'label' => 'form.filter.key',
            ), $options))
            ->add('value', 'text', $this->decorateOption(array(
                'required' => false,
                'label' => 'form.filter.value',
            ), $options))
        ;
    }

    public function getName()
    {
        return 'translation_filter';
    }
}
