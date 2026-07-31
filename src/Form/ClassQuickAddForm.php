<?php

namespace App\Form;

use App\Entity\OntoClass;
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClassQuickAddForm extends AbstractType
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
            ->add('identifierInNamespace', TextType::class, array(
            ))
            ->add('labels', CollectionType::class, array(
                'label' => 'Enter a label and select a language',
                'entry_type' => LabelType::class,
                'entry_options' => array('label' => false),
                'error_bubbling' => false,
                'allow_add' => true,
                'by_reference' => false,
            ))
            ->add('textProperties', CollectionType::class, array(
                'entry_type' => TextPropertyType::class,
                'entry_options' => array('label' => false),
                'error_bubbling' => false,
                'allow_add' => true,
                'by_reference' => false,
            ));

        if($options['is_external']){
            $builder->add('identifierInUri', TextType::class, array(
                'label' => 'Set an identifier for URI',
                'data' => $options['identifier_in_uri_prefilled'],
                'attr' => array('data-uri-param' => $options['uri_param'], 'data-default-value' => $options['identifier_in_uri_prefilled'])
            ));
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OntoClass::class,
            "allow_extra_fields" => true,
            'uri_param' => 0,
            'identifier_in_uri_prefilled' => '',
            'is_external' => false
        ]);
    }
}
