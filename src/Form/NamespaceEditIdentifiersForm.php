<?php

namespace App\Form;

use App\Entity\OntoNamespace;
use App\Entity\User;
use App\Form\DataTransformer\OntoClassToNumberTransformer;
use App\Form\DataTransformer\UserToNumberTransformer;
use App\Repository\NamespaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NamespaceEditIdentifiersForm extends AbstractType
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
            ->add('classPrefix', TextType::class, array(
                'label' => false,
                'error_bubbling' => true
            ))
            ->add('propertyPrefix', TextType::class, array(
                'label' => false,
                'error_bubbling' => true
            ))
            ->add('currentClassNumber', IntegerType::class, array(
                'label' => false,
                'error_bubbling' => true
            ))
            ->add('currentPropertyNumber', IntegerType::class, array(
                'label' => false,
                'error_bubbling' => true
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OntoNamespace::class,
            "allow_extra_fields" => true
        ]);
    }
}
