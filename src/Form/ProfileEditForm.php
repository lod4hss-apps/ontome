<?php

namespace App\Form;

use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\User;
use App\Form\DataTransformer\UserToNumberTransformer;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileEditForm extends AbstractType
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
            throw new \LogicException('The ProfileEditForm cannot be used without an authenticated user!');
        }

        $builder
            ->add('creator', HiddenType::class)
            ->add('modifier', HiddenType::class);
        $builder->get('creator')
            ->addModelTransformer($this->transformer);
        $builder->get('modifier')
            ->addModelTransformer($this->transformer);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
            $profile = $event->getData();
            $form = $event->getForm();

            // checks if the Profile object is a "root" or not
            if ($profile->getIsRootProfile()) {
                $form->add('projectOfBelonging', EntityType::class,
                    [
                        'class' => Project::class,
                        'label' => 'Project of belonging',
                        'query_builder' => fn (ProjectRepository $repo) => $repo->findAvailableProjectByAdminId($user),
                    ]
                );
                $form->add('isForcedPublication', HiddenType::class);
            } elseif ($profile->isPublishable()) {
                $form->add('isForcedPublication', CheckboxType::class, [
                    'label' => 'Allow API connection',
                ]);
                $form->add('projectOfBelonging', HiddenType::class);
            } else {
                $form->add('isForcedPublication', HiddenType::class);
                $form->add('projectOfBelonging', HiddenType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
