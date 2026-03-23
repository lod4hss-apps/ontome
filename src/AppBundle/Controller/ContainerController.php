<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Label;
use AppBundle\Entity\Container;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContainerController extends Controller
{
    /**
     * @Route("/container/{id}/fetch", name="container")
     * @Method("GET")
     */
    public function getContainer(Request $request)
    {
        $id = $request->get('id');

        $container = $this->getDoctrine()->getRepository('AppBundle:Container')->find($id);

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

        $containerData = [
            'id' => $container->getId(),
            'label' => $label,
            'namespaces' => $namespacesData,
            'isOngoing' => $container->getIsOngoing()
        ];

        if (!$container) {
            throw new NotFoundHttpException("Container not found");
        }
        return new JsonResponse($containerData);
    }

    /**
     * @Route("/container/{id}/root-namespaces-not-associated/fetch", name="container_root_namespaces_not_associated")
     * @Method("GET")
     */
    public function getRootNamespacesNotAssociated(Request $request)
    {
        $id = $request->get('id');

        $container = $this->getDoctrine()->getRepository('AppBundle:Container')->find($id);

        if (!$container) {
            throw new NotFoundHttpException("Container not found");
        }

        $idRootNamespacesAssociated = [];
        foreach ($container->getNamespaces() as $namespace) {
            $idRootNamespacesAssociated[] = $namespace->getTopLevelNamespace()->getId();
        }

        $qb = $this->getDoctrine()->getRepository('AppBundle:OntoNamespace')->createQueryBuilder('n')
            ->where('n.isTopLevelNamespace = :isTop')
            ->setParameter('isTop', true);

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
     * @Route("/container/create", name="container_create")
     * @Method("POST")
     */
    public function createContainer(Request $request)
    {
        $projectId = $request->get('project_id');
        $label = $request->get('label');
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($projectId);

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

        $em = $this->getDoctrine()->getManager();
        $em->persist($newLabel);
        $em->persist($newContainer);
        $em->flush();

        return new JsonResponse([
            'status' => 'Success',
            'message' => 'Container created successfully'
        ]);
    }   

    /**
     * @Route("/association_container_namespace/create", name="association_container_namespace_create")
     * @Method("POST")
     */
    public function createAssociationContainerNamespace(Request $request)
    {
        // Il faudra rajouter le droit d'accès à cette route pour éviter que n'importe qui puisse faire n'importe quoi

        $containerId = $request->get('container_id');
        $namespaceId = $request->get('namespace_id');

        $container = $this->getDoctrine()->getRepository('AppBundle:Container')->find($containerId);
        $namespace = $this->getDoctrine()->getRepository('AppBundle:OntoNamespace')->find($namespaceId);

        $container->addNamespace($namespace);
        $container->setModifier($this->getUser());
        $container->setModificationTime(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($container);
        $em->flush();

        return new JsonResponse([
            'status' => 'Success',
            'message' => 'Association created successfully'
        ]);
    }

    /**
     * @Route("/association_container_namespace/{containerId}/{namespaceId}/delete", name="association_container_namespace_delete")
     * @Method("GET")
     */
    public function deleteAssociationContainerNamespace(Request $request)
    {
        // Il faudra rajouter le droit d'accès à cette route pour éviter que n'importe qui puisse faire n'importe quoi
        
        $containerId = $request->get('containerId');
        $namespaceId = $request->get('namespaceId');

        $container = $this->getDoctrine()->getRepository('AppBundle:Container')->find($containerId);
        $namespace = $this->getDoctrine()->getRepository('AppBundle:OntoNamespace')->find($namespaceId);

        $container->removeNamespace($namespace);
        $container->setModifier($this->getUser());
        $container->setModificationTime(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($container);
        $em->flush();

        return new JsonResponse([
            'status' => 'Success',
            'message' => 'Association deleted successfully'
        ]);
    }
}