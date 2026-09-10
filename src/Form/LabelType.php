<?php

namespace App\Form;

use App\Entity\Label;
use App\Form\DataTransformer\UserToNumberTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LabelType extends AbstractType
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
            throw new \LogicException('The LabelType cannot be used without an authenticated user!');
        }

        $builder
            ->add('label', TextType::class, ['label' => false,
            ]);
        if ($options['canInverseLabel']) {
            $builder
                ->add('inverseLabel', TextType::class, [
                ]);
        }
        $builder
            ->add('languageIsoCode', ChoiceType::class, [
                'choices' => [
                    'English' => 'en',
                    'French' => 'fr',
                    // 'Canadian French' => 'fr-CA',
                    'German' => 'de',
                    'Greek' => 'el',
                    'Spanish' => 'es',
                    'Italian' => 'it',
                    'Portuguese' => 'pt',
                    'Russian' => 'ru',
                    'Chinese' => 'zh',
                ],
                'label' => 'Language',
            ])
            ->add('isStandardLabelForLanguage', HiddenType::class, [
                'data' => true,
            ])
            ->add('creator', HiddenType::class)
            ->add('modifier', HiddenType::class);
        $builder->get('creator')
            ->addModelTransformer($this->transformer);
        $builder->get('modifier')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Label::class,
            'canInverseLabel' => false,
        ]);
    }
}
