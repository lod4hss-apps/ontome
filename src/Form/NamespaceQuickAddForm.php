<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 02/12/2018
 * Time: 09:19.
 */

namespace App\Form;

use App\Entity\OntoNamespace;
use App\Entity\TextProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NamespaceQuickAddForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isExternalNamespace', CheckboxType::class, [
                'label' => 'External namespace',
            ])
            ->add('namespaceURI', UrlType::class, [
                'label' => 'Namespace URI',
                'default_protocol' => 'http',
            ])
            ->add('uriGenerator', TextType::class, [
                'label' => 'OntoME URI generator',
                'mapped' => false,
            ])
            ->add('rootNamespacePrefix', TextType::class, [
                'label' => 'Root namespace prefix',
            ])
            ->add('labels', CollectionType::class, [
                'label' => 'Enter a label and select a language',
                'entry_type' => LabelType::class,
                'entry_options' => ['label' => false],
                'error_bubbling' => false,
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('automaticIdentifierManagement', CheckboxType::class, [
                'mapped' => false,
                'data' => true,
                'label' => 'Automatic identifier management',
            ])
            ->add('classPrefix', TextType::class, [
                'label' => 'Class prefix',
                'attr' => [
                    'size' => '6',
                    'maxlength' => '6',
                ],
            ])
            ->add('propertyPrefix', TextType::class, [
                'label' => 'Property prefix',
                'attr' => [
                    'size' => '6',
                    'maxlength' => '6',
                ],
            ])
            ->add('textProperties', CollectionType::class, [
                'label' => 'Enter a description and select a language',
                'entry_type' => TextPropertyType::class,
                'entry_options' => ['label' => false],
                'error_bubbling' => false,
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('contributors', TextPropertyType::class, [
                'label' => 'Contributors to the ongoing namespace',
                'error_bubbling' => false,
                'by_reference' => false,
                'mapped' => false,
                'systemType' => 2, // Contributors: pas besoin d'éditeur enrichi,
            ])
            ->add('referenceNamespaces', HiddenType::class, [
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OntoNamespace::class,
            'allow_extra_fields' => true,
            'validation_groups' => ['Default', 'Description'],
            'txtpContributors' => new TextProperty(),
        ]);
    }
}
