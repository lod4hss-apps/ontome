<?php

namespace App\Form;

use App\Entity\Property;
use App\Form\DataTransformer\UserToNumberTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PropertyEditUriIdentifierForm extends AbstractType
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
        $builder
            ->add('identifierInURI', TextType::class, array(
                'label' => 'Identifier in URI'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
            "allow_extra_fields" => true
        ]);
    }
}
