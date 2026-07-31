<?php

namespace App\Controller;

use App\Entity\Label;
use App\Entity\Container;
use App\Repository\ContainerRepository;
use App\Repository\NamespaceRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Persistence\ManagerRegistry;

class ContainerController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        // Inject the ManagerRegistry into the controller
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/container/{id}/fetch", name="container", methods={"GET"})
     * @param Request $request
     * @return JsonResponse a JSON formatted response of the container with its namespaces and pathbuilders
     */
    public function getContainer(Request $request, ContainerRepository $containerRepository)
    {
        $id = $request->get('id');

        $container = $containerRepository->find($id);

        if (!$container) {
            throw new NotFoundHttpException("Container not found");
        }

        $label = $container->getLabel()->getLabel();

        $namespaces = $container->getNamespaces();
        $namespacesData = [];
        foreach ($namespaces as $namespace) {
            $referencedNamespaces = $namespace->getAllReferencedNamespaces();

            $referencedNamespacesData = [];
            foreach ($referencedNamespaces as $referencedNamespace) {
                $referencedNamespacesData[] = [
                    'id' => $referencedNamespace->getId(),
                    'standardLabel' => $referencedNamespace->getStandardLabel()
                ];
            }

            $namespacesData[] = [
                'id' => $namespace->getId(),
                'standardLabel' => $namespace->getStandardLabel(),
                'referencedNamespaces' => $referencedNamespacesData
            ];
        }

        $pathbuilders = $container->getPathbuilders();
        $pathbuildersData = [];
        foreach ($pathbuilders as $pathbuilder) {
            $pathbuildersData[] = [
                'id' => $pathbuilder->getId(),
                'label' => $pathbuilder->getLabel()->getLabel()
            ];
        }

        $containerData = [
            'id' => $container->getId(),
            'label' => $label,
            'namespaces' => $namespacesData,
            'pathbuilders' => $pathbuildersData,
            'isOngoing' => $container->getIsOngoing()
        ];
        return new JsonResponse($containerData);
    }

    /**
     * @Route("/container/{id}/root-namespaces-not-associated/fetch", name="container_root_namespaces_not_associated", methods={"GET"})
     * @param Request $request
     * @return JsonResponse a JSON formatted response of the root namespaces not associated with the container
     */
    public function getRootNamespacesNotAssociated(Request $request, ContainerRepository $containerRepository, NamespaceRepository $namespaceRepository)
    {
        $id = $request->get('id');

        $container = $containerRepository->find($id);

        if (!$container) {
            throw new NotFoundHttpException("Container not found");
        }

        // Ensemble de namespaces déjà associés au container => on récupère leur id root
        $idRootNamespacesAssociated = [];
        foreach ($container->getNamespaces() as $namespace) {
            $idRootNamespacesAssociated[] = $namespace->getTopLevelNamespace()->getId();
        }

        // Récupération de tous les root namespaces qui ne sont pas associés au container
        $qb = $namespaceRepository->createQueryBuilder('n')
            ->select('DISTINCT n')
            ->innerJoin('App:OntoNamespace', 'child', 'WITH', 'child.topLevelNamespace = n')
            ->where('n.isTopLevelNamespace = :isTop')
            ->andWhere('child.isVisible = :isVisible')
            ->andWhere('child.id != n.id')
            ->setParameter('isTop', true)
            ->setParameter('isVisible', true)
            ->orderBy('n.standardLabel', 'ASC');

        if (!empty($idRootNamespacesAssociated)) {
            $qb->andWhere('n.id NOT IN (:ids)')
                ->setParameter('ids', $idRootNamespacesAssociated);
        }

        $rootNamespacesNotAssociated = $qb->getQuery()->getResult();

        $rootNamespacesNotAssociatedData = [];
        foreach ($rootNamespacesNotAssociated as $rootNamespace) {
            $rootNamespacesNotAssociatedData[] = [
                'id' => $rootNamespace->getId(),
                'standardLabel' => $rootNamespace->getStandardLabel(),
            ];
        }

        return new JsonResponse($rootNamespacesNotAssociatedData);
    }

    /**
     * @Route("/container/create", name="container_create", methods={"POST"})
     * @param Request $request
     * @return JsonResponse a JSON formatted response indicating the result of the container creation
     */
    public function createContainer(Request $request, ProjectRepository $projectRepository)
    {
        $projectId = $request->get('project_id');
        $label = $request->get('label');
        $project = $projectRepository->find($projectId);

        $newLabel = new Label();
        $newLabel->setLabel($label);
        $newLabel->setLanguageIsoCode('en');
        $newLabel->setCreator($this->getUser());
        $newLabel->setModifier($this->getUser());
        $newLabel->setCreationTime(new \DateTime());
        $newLabel->setModificationTime(new \DateTime());

        $newContainer = new Container();
        $newContainer->setLabel($newLabel);
        $newContainer->setProject($project);
        $newContainer->setIsOngoing(true);
        $newContainer->setCreator($this->getUser());
        $newContainer->setModifier($this->getUser());
        $newContainer->setCreationTime(new \DateTime());
        $newContainer->setModificationTime(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($newLabel);
        $em->persist($newContainer);
        $em->flush();

        return new JsonResponse([
            'status' => 'Success',
            'message' => 'Container created successfully'
        ]);
    }

    /**
     * @Route("/association_container_namespace/create", name="association_container_namespace_create", methods={"POST"})
     * @param Request $request
     * @return JsonResponse a JSON formatted response indicating the result of the association creation
     */
    public function createAssociationContainerNamespace(Request $request, ContainerRepository $containerRepository, NamespaceRepository $namespaceRepository)
    {

        $containerId = $request->get('container_id');
        $namespaceId = $request->get('namespace_id');

        $container = $containerRepository->find($containerId);
        $namespace = $namespaceRepository->find($namespaceId);

        if (!$container || !$namespace) {
            return new JsonResponse(['status' => 'Error', 'message' => 'Container or Namespace not found'], 404);
        }

        // Droit d'accès à cette route pour éviter que n'importe qui puisse faire n'importe quoi
        $this->denyAccessUnlessGranted('edit', $container->getProject());

        $container->addNamespace($namespace);
        $container->setModifier($this->getUser());
        $container->setModificationTime(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($container);
        $em->flush();

        return new JsonResponse([
            'status' => 'Success',
            'message' => 'Association created successfully'
        ]);
    }

    /**
     * @Route("/association_container_namespace/{containerId}/{namespaceId}/delete", name="association_container_namespace_delete", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse a JSON formatted response indicating the result of the association deletion
     */
    public function deleteAssociationContainerNamespace(Request $request, ContainerRepository $containerRepository, NamespaceRepository $namespaceRepository)
    {
        // Il faudra rajouter le droit d'accès à cette route pour éviter que n'importe qui puisse faire n'importe quoi

        $containerId = $request->get('containerId');
        $namespaceId = $request->get('namespaceId');

        $container = $containerRepository->find($containerId);
        $namespace = $namespaceRepository->find($namespaceId);

        $container->removeNamespace($namespace);
        $container->setModifier($this->getUser());
        $container->setModificationTime(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($container);
        $em->flush();

        return new JsonResponse([
            'status' => 'Success',
            'message' => 'Association deleted successfully'
        ]);
    }
}