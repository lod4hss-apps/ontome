<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 15/01/2018
 * Time: 15:19.
 */

namespace App\Controller;

use App\Entity\ProjectAssociation;
use App\Entity\SystemType;
use App\Form\PublicProjectNamespaceAssociationAddForm;
use App\Repository\ProjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjectAssociationController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        // Inject the ManagerRegistry into the controller
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/public-project-namespace-association/new", name="public_project_namespace_association_new")
     */
    public function newPublicProjectNamespaceAssociationAction(Request $request, ProjectRepository $projectRepository)
    {
        $projectAssociation = new ProjectAssociation();
        $publicProject = $projectRepository->find(21); // public project = 21

        $this->denyAccessUnlessGranted('full_edit', $publicProject); // admins only can add a new namespace to the public project

        $em = $this->doctrine->getManager();
        $systemType = $em->getRepository(SystemType::class)->find(17); // systemType 17 = Default display

        $form = $this->createForm(PublicProjectNamespaceAssociationAddForm::class, $projectAssociation);

        // only handles data on POST
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $projectAssociation = $form->getData();
            $projectAssociation->setProject($publicProject);
            $projectAssociation->setSystemType($systemType);
            $projectAssociation->setCreator($this->getUser());
            $projectAssociation->setModifier($this->getUser());
            $projectAssociation->setCreationTime(new \DateTime('now'));
            $projectAssociation->setModificationTime(new \DateTime('now'));

            $em->persist($projectAssociation);
            $em->flush();

            return $this->redirectToRoute('project_edit', [
                'id' => $projectAssociation->getProject()->getId(),
                '_fragment' => 'managed-namespaces',
            ]);
        }

        return $this->render('projectAssociation/newPublicProjectNamespaceAssociation.html.twig', [
            'projectAssociation' => $projectAssociation,
            'publicProjectNamespaceAssociationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/project-association/{id}/delete", name="project_association_delete", requirements={"id"="^([0-9]+)|(associationID){1}$"})
     */
    public function deleteAction(ProjectAssociation $projectAssociation)
    {
        $this->denyAccessUnlessGranted('full_edit', $projectAssociation->getProject());

        if (!$projectAssociation) {
            throw $this->createNotFoundException('This project association does not exist');
        }

        try {
            $em = $this->doctrine->getManager();
            $em->remove($projectAssociation);
            $em->flush();
        } catch (\Exception $e) {
            return new \Exception('Something went wrong!');
        }

        return $this->redirectToRoute('project_edit', [
            'id' => $projectAssociation->getProject()->getId(),
            '_fragment' => 'managed-namespaces',
        ]);
    }
}
