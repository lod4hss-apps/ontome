<?php

namespace App\Form;

use App\Entity\Property;
use App\Entity\User;
use App\Form\DataTransformer\UserToNumberTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OutgoingPropertyQuickAddForm extends AbstractType
{
    private $transformer;

    private $tokenStorage;

    private $em;

    public function __construct(UserToNumberTransformer $transformer, TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->transformer = $transformer;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userID = $this->tokenStorage->getToken()->getUser()->getId();
        $user = $this->em->getRepository(User::class)->find($userID);

        if (!$user) {
            throw new \LogicException('The OutgoingPropertyQuickAddForm cannot be used without an authenticated user!');
        }

        $choices = [];
        foreach ($options['classesVersion'] as $cv) {
            if ($cv['standardLabel'] != $cv['identifierInNamespace']) {
                $choices[$cv['rootNamespacePrefix'].':'.$cv['standardLabel'].' – '.$cv['identifierInNamespace']] = $cv['id'];
            } else {
                $choices[$cv['rootNamespacePrefix'].':'.$cv['standardLabel']] = $cv['id'];
            }
        }

        $builder
            ->add('identifierInNamespace', TextType::class, [
            ])
            ->add('labels', CollectionType::class, [
                'label' => 'Enter a label and select a language',
                'entry_type' => LabelType::class,
                'entry_options' => ['label' => false, 'canInverseLabel' => true],
                'error_bubbling' => false,
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('rangeVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => $choices,
            ])
            ->add('domainMinQuantifierVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Min' => null,
                    '0' => 0,
                    '1' => 1,
                    'n' => -1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'preferred_choices' => [null, 0, 1, -1],
            ])
            ->add('domainMaxQuantifierVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Max' => null,
                    '1' => 1,
                    'n' => -1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'preferred_choices' => [null, 1, -1],
            ])
            ->add('rangeMinQuantifierVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Min' => null,
                    '0' => 0,
                    '1' => 1,
                    'n' => -1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'preferred_choices' => [null, 0, 1, -1],
            ])
            ->add('rangeMaxQuantifierVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Max' => null,
                    '1' => 1,
                    'n' => -1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'preferred_choices' => [null, 1, -1],
            ])
            ->add('textProperties', CollectionType::class, [
                'entry_type' => TextPropertyType::class,
                'entry_options' => ['label' => false],
                'error_bubbling' => false,
                'allow_add' => true,
                'by_reference' => false,
            ]);

        if ($options['is_external']) {
            $builder->add('identifierInUri', TextType::class, [
                'label' => 'Set an identifier for URI',
                'data' => $options['identifier_in_uri_prefilled'],
                'attr' => ['data-uri-param' => $options['uri_param'], 'data-default-value' => $options['identifier_in_uri_prefilled']],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
            'allow_extra_fields' => true,
            'classesVersion' => null,
            'uri_param' => 0,
            'identifier_in_uri_prefilled' => '',
            'is_external' => false,
        ]);
    }
}
