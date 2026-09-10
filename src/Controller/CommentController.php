<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 15/10/2018
 * Time: 10:17.
 */

namespace App\Controller;

use App\Entity\ClassAssociation;
use App\Entity\Comment;
use App\Entity\EntityAssociation;
use App\Entity\Label;
use App\Entity\Property;
use App\Entity\PropertyAssociation;
use App\Form\CommentForm;
use App\Repository\ClassRepository;
use App\Repository\ClassVersionRepository;
use App\Repository\LabelRepository;
use App\Repository\NamespaceRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyVersionRepository;
use App\Repository\TextPropertyRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        // Inject the ManagerRegistry into the controller
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/comment/{objectType}/{objectId}/json",
     *     name="comment_show_json",
     *     requirements={"objectType"="^(class|property|class-association|property-association|entity-association|text-property|label|namespace){1}$","objectId"="^[0-9]+$"},
     *     methods={"GET"})
     *
     * @param string $objectType The object type name
     * @param int    $objectId   The id of the object
     *
     * @return JsonResponse a Json formatted comments list
     */
    public function getCommentsAction(string $objectType, string $objectId, ClassRepository $classRepository, PropertyRepository $propertyRepository, TextPropertyRepository $textPropertyRepository, LabelRepository $labelRepository, NamespaceRepository $namespaceRepository): JsonResponse
    {
        $em = $this->doctrine->getManager();

        // $objectType = 'class';
        // $objectId = 268;
        if ($objectType === 'class') {
            $associatedEntity = $classRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The class n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'property') {
            $associatedEntity = $propertyRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The property n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'class-association') {
            $associatedEntity = $em->getRepository(ClassAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The class association n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'property-association') {
            $associatedEntity = $em->getRepository(PropertyAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The property association n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'entity-association') {
            $associatedEntity = $em->getRepository(EntityAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The entity association n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'text-property') {
            $associatedEntity = $textPropertyRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The text property n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'label') {
            $associatedEntity = $labelRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The label n° '.$objectId.' does not exist');
            }
        } elseif ($objectType === 'namespace') {
            $associatedEntity = $namespaceRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The namespace n° '.$objectId.' does not exist');
            }
        } else {
            throw $this->createNotFoundException('The requested object "'.$objectType.'" does not exist!');
        }

        $comments = [];

        foreach ($associatedEntity->getComments() as $comment) {
            $comments[] = [
                'id' => $comment->getId(),
                'creator' => $comment->getCreator(),
                'text' => $comment->getComment(),
                'creationTime' => $comment->getCreationTime()->format('M d, Y'),
            ];
        }

        $data = [
            'comments' => $comments,
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/comment/new/{object}/{objectId}", name="comment_new", requirements={"object"="^(class-version|property-version|class-association|property-association|entity-association|text-property|label|namespace){1}$","objectId"="^[0-9]+$"}, methods={"POST"})
     */
    public function newAction(string $object, string $objectId, Request $request, ClassVersionRepository $classVersionRepository, NamespaceRepository $namespaceRepository, PropertyVersionRepository $propertyVersionRepository, TextPropertyRepository $textPropertyRepository, LabelRepository $labelRepository)
    {
        $em = $this->doctrine->getManager();

        $comment = new Comment();

        if ($object === 'class-version') {
            $classVersion = $classVersionRepository->find($objectId);
            $associatedEntity = $classVersion->getClass();
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The class n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = $namespaceRepository->find($classVersion->getNamespaceForVersion());
            $comment->setClass($associatedEntity);
        } elseif ($object === 'property-version') {
            $propertyVersion = $propertyVersionRepository->find($objectId);
            $associatedEntity = $propertyVersion->getProperty();
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The property n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = $namespaceRepository->find($propertyVersion->getNamespaceForVersion());
            $comment->setProperty($associatedEntity);
        } elseif ($object === 'class-association') {
            $associatedEntity = $em->getRepository(ClassAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The class association n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = $namespaceRepository->find($associatedEntity->getNamespaceForVersion());
            $comment->setClassAssociation($associatedEntity);
        } elseif ($object === 'property-association') {
            $associatedEntity = $em->getRepository(PropertyAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The property association n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = $namespaceRepository->find($associatedEntity->getNamespaceForVersion());
            $comment->setPropertyAssociation($associatedEntity);
        } elseif ($object === 'entity-association') {
            $associatedEntity = $em->getRepository(EntityAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The entity association n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = $namespaceRepository->find($associatedEntity->getNamespaceForVersion());
            $comment->setEntityAssociation($associatedEntity);
        } elseif ($object === 'text-property') {
            $associatedEntity = $textPropertyRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The text property n° '.$objectId.' does not exist');
            }
            if (!is_null($associatedEntity->getNamespace()) and !is_null($associatedEntity->getProfile()) and !is_null($associatedEntity->getProject())) {
                $namespaceForVersion = $namespaceRepository->find($associatedEntity->getNamespaceForVersion());
            } else {
                $namespaceForVersion = null;
            }
            $comment->setTextProperty($associatedEntity);
        } elseif ($object === 'label') {
            $associatedEntity = $labelRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The label n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = null;
            $comment->setLabel($associatedEntity);
        } elseif ($object === 'namespace') {
            $associatedEntity = $namespaceRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The namespace n° '.$objectId.' does not exist');
            }
            $namespaceForVersion = $associatedEntity;
            $comment->setNamespace($associatedEntity);
        } else {
            throw $this->createNotFoundException('The requested object "'.$object.'" does not exist!');
        }

        // $this->denyAccessUnlessGranted('edit', $associatedObject);

        if (!in_array($this->getUser()->getId(), $comment->getViewedBy())) {
            $viewedBy = $comment->getViewedBy();
            $viewedBy[] = $this->getUser()->getId();
            $comment->setViewedBy($viewedBy);
        }

        $comment->setCreator($this->getUser());
        $comment->setModifier($this->getUser());
        $comment->setCreationTime(new \DateTime('now'));
        $comment->setModificationTime(new \DateTime('now'));
        $comment->setNamespaceForVersion($namespaceForVersion);

        $form = $this->createForm(CommentForm::class, $comment);

        $status = 'error';
        $message = '';

        // only handles data on POST
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setCreator($this->getUser());
            $comment->setModifier($this->getUser());
            $comment->setCreationTime(new \DateTime('now'));
            $comment->setModificationTime(new \DateTime('now'));
            $comment->setNamespaceForVersion($namespaceForVersion);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $html = $this->renderView('comment/new.html.twig', [
                'comment' => $comment,
                'commentForm' => $form->createView(),
            ]);

            try {
                $em->flush();
                $status = 'Success';
                $message = 'New comment saved';
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }

            $response = [
                'status' => $status,
                'message' => $message,
                'html' => $html,
            ];

            return new JsonResponse($response);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $status = 'Error';
            $message = 'Invalid form validation';
            $html = $this->renderView('comment/new.html.twig', [
                'comment' => $comment,
                'commentForm' => $form->createView(),
            ]);
            $response = [
                'status' => $status,
                'message' => $message,
                'html' => $html,
            ];

            return new JsonResponse($response);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'commentForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comment/{object}/{objectId}/viewedby/json", name="viewed_by_json", requirements={"object"="^(class|property|class-association|property-association|entity-association|text-property|label|namespace|selectedObject){1}$","objectId"="^([0-9]+)|(selectedValue){1}$"}, methods={"GET"})
     */
    public function viewedByJson(string $object, string $objectId, ClassRepository $classRepository, PropertyRepository $propertyRepository, TextPropertyRepository $textPropertyRepository, LabelRepository $labelRepository, NamespaceRepository $namespaceRepository): JsonResponse
    {
        $em = $this->doctrine->getManager();

        if ($object === 'class') {
            $associatedEntity = $classRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The class n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['class' => $associatedEntity]);
        } elseif ($object === 'property') {
            $associatedEntity = $propertyRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The property n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['property' => $associatedEntity]);
        } elseif ($object === 'class-association') {
            $associatedEntity = $em->getRepository(ClassAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The class association n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['classAssociation' => $associatedEntity]);
        } elseif ($object === 'property-association') {
            $associatedEntity = $em->getRepository(PropertyAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The property association n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['propertyAssociation' => $associatedEntity]);
        } elseif ($object === 'entity-association') {
            $associatedEntity = $em->getRepository(EntityAssociation::class)->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The entity association n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['entityAssociation' => $associatedEntity]);
        } elseif ($object === 'text-property') {
            $associatedEntity = $textPropertyRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The text property n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['textProperty' => $associatedEntity]);
        } elseif ($object === 'label') {
            $associatedEntity = $labelRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The label n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['label' => $associatedEntity]);
        } elseif ($object === 'namespace') {
            $associatedEntity = $namespaceRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The namespace n° '.$objectId.' does not exist');
            }

            $comments = $em->getRepository(Comment::class)->findBy(['namespace' => $associatedEntity]);
        } else {
            throw $this->createNotFoundException('The requested object "'.$object.'" does not exist!');
        }

        foreach ($comments as $comment) {
            if (!in_array($this->getUser()->getId(), $comment->getViewedBy())) {
                $viewedBy = $comment->getViewedBy();
                $viewedBy[] = $this->getUser()->getId();
                $comment->setViewedBy($viewedBy);
                $em->persist($comment);
                $em->flush();
            }
        }

        $response = [
            'status' => 200,
            'message' => 'OK',
        ];

        return new JsonResponse($response);
    }
}
