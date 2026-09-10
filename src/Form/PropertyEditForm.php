<?php

namespace App\Form;

use App\Entity\PropertyVersion;
use App\Form\DataTransformer\UserToNumberTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PropertyEditForm extends AbstractType
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
            throw new \LogicException('The ParentClassAssociationForm cannot be used without an authenticated user!');
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
            ->add('domainVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => $choices,
                'data' => $options['defaultDomain'],
            ])
            ->add('rangeVersion', ChoiceType::class, [
                'mapped' => false,
                'choices' => $choices,
                'data' => $options['defaultRange'],
            ])
            ->add('domainMinQuantifier', ChoiceType::class, [
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
            ->add('domainMaxQuantifier', ChoiceType::class, [
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
            ->add('rangeMinQuantifier', ChoiceType::class, [
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
            ->add('rangeMaxQuantifier', ChoiceType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PropertyVersion::class,
            'allow_extra_fields' => true,
            'classesVersion' => null,
            'defaultDomain' => null,
            'defaultRange' => null,
        ]);
    }
}
