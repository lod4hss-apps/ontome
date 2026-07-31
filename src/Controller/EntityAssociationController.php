<?php
/**
 * Created by PhpStorm.
 * User: pc-alexandre-pro
 * Date: 07/05/2019
 * Time: 14:39
 */

namespace App\Controller;


use App\Entity\EntityAssociation;
use App\Entity\OntoClass;
use App\Entity\Property;
use App\Entity\SystemType;
use App\Entity\TextProperty;
use App\Form\EntityAssociationForm;
use App\Form\EntityAssociationEditForm;
use App\Repository\ClassRepository;
use App\Repository\ClassVersionRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyVersionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Persistence\ManagerRegistry;

class EntityAssociationController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        // Inject the ManagerRegistry into the controller
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/entity-association/new/{object}/{objectId}", name="new_entity_association_form", requirements={"object"="^(class|property){1}$","objectId"="^[0-9]+$"})
     */
    public function newEntityAssociationAction(Request $request, $object, $objectId, ClassRepository $classRepository, ClassVersionRepository $classVersionRepository, PropertyRepository $propertyRepository, PropertyVersionRepository $propertyVersionRepository)
    {
        $em = $this->doctrine->getManager();

        $entityAssociation = new EntityAssociation();

        if($object == 'class')
        {
            $source = $classRepository->find($objectId);
            if (!$source) {
                throw $this->createNotFoundException('The class n° '.$objectId.' does not exist');
            }
            $entityAssociation->setSourceClass($source);
            $namespaceForEntityVersion = $source->getClassVersionForDisplay()->getNamespaceForVersion();
        }
        elseif($object == 'property')
        {
            $source = $propertyRepository->find($objectId);
            if (!$source) {
                throw $this->createNotFoundException('The property n° '.$objectId.' does not exist');
            }
            $entityAssociation->setSourceProperty($source);
            $namespaceForEntityVersion = $source->getPropertyVersionForDisplay()->getNamespaceForVersion();
        }

        $objectNamespaceId = null;
        if($source instanceof OntoClass){
            $objectNamespaceId = $source->getClassVersionForDisplay()->getNamespaceForVersion();
        }
        elseif($source instanceof Property){
            $objectNamespaceId = $source->getPropertyVersionForDisplay()->getNamespaceForVersion();
        }
        $this->denyAccessUnlessGranted('add_associations', $objectNamespaceId);

        $systemTypeJustification = $em->getRepository(SystemType::class)->find(15); //systemType 15 = justification
        $systemTypeExample = $em->getRepository(SystemType::class)->find(7); //systemType 1 = example

        $justification = new TextProperty();
        $justification->setEntityAssociation($entityAssociation);
        $justification->setSystemType($systemTypeJustification);
        $justification->setNamespaceForVersion($this->getUser()->getCurrentOngoingNamespace());
        $justification->setCreator($this->getUser());
        $justification->setModifier($this->getUser());
        $justification->setCreationTime(new \DateTime('now'));
        $justification->setModificationTime(new \DateTime('now'));

        $entityAssociation->addTextProperty($justification);

        // Filtrage
        $namespacesId[] = $this->getUser()->getCurrentOngoingNamespace()->getId();

        // Sans oublier les namespaces références si indisponibles
        foreach($this->getUser()->getCurrentOngoingNamespace()->getAllReferencedNamespaces() as $referencedNamespaces){
            if(!in_array($referencedNamespaces->getId(), $namespacesId)){
                $namespacesId[] = $referencedNamespaces->getId();
            }
        }

        $entityAssociation->setSourceNamespaceForVersion($namespaceForEntityVersion);

        if($entityAssociation->getSourceObjectType() == "class"){
            $arrayEntitiesVersion = $classVersionRepository->findIdAndStandardLabelOfClassesVersionByNamespacesId($namespacesId);

            if(!$entityAssociation->getSourceClass()->getIsRecursive()){
                foreach ($arrayEntitiesVersion as $cv){
                    if($cv['id'] == $objectId){
                        unset($arrayEntitiesVersion[array_search($cv, $arrayEntitiesVersion)]);
                    }
                }
            }
        }
        elseif($entityAssociation->getSourceObjectType() == "property"){
            $arrayEntitiesVersion = $propertyRepository->findIdAndStandardLabelOfPropertiesVersionByNamespacesId($namespacesId);
            if(!$entityAssociation->getSourceProperty()->getIsRecursive()){
                foreach ($arrayEntitiesVersion as $pv){
                    if($pv['id'] == $objectId){
                        unset($arrayEntitiesVersion[array_search($pv, $arrayEntitiesVersion)]);
                    }
                }
            }
        }

        $form = $this->createForm(EntityAssociationForm::class, $entityAssociation, array(
            'object' => $object,
            'entitiesVersion' => $arrayEntitiesVersion));

        // only handles data on POST
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($entityAssociation->getSourceObjectType() == 'class'){
                $targetClass = $classRepository->find($form->get("targetClassVersion")->getData());
                $entityAssociation->setTargetClass($targetClass);
                $targetNamespace = $classVersionRepository->findClassVersionByClassAndNamespacesId($targetClass, $namespacesId)
                    ->getNamespaceForVersion();
                $entityAssociation->setTargetNamespaceForVersion($targetNamespace);
            }
            elseif($entityAssociation->getSourceObjectType() == 'property'){
                $targetProperty = $propertyRepository->find($form->get("targetPropertyVersion")->getData());
                $entityAssociation->setTargetProperty($targetProperty);
                $targetNamespace = $propertyVersionRepository->findPropertyVersionByPropertyAndNamespacesId($targetProperty, $namespacesId)
                    ->getNamespaceForVersion();
                $entityAssociation->setTargetNamespaceForVersion($targetNamespace);
            }

            $entityAssociation = $form->getData();
            $entityAssociation->setNamespaceForVersion($this->getUser()->getCurrentOngoingNamespace());
            $entityAssociation->setCreator($this->getUser());
            $entityAssociation->setModifier($this->getUser());
            $entityAssociation->setCreationTime(new \DateTime('now'));
            $entityAssociation->setModificationTime(new \DateTime('now'));
            $entityAssociation->setDirected(FALSE);

            if ($entityAssociation->getTextProperties()->containsKey(1)) {
                $entityAssociation->getTextProperties()[1]->setCreationTime(new \DateTime('now'));
                $entityAssociation->getTextProperties()[1]->setModificationTime(new \DateTime('now'));
                $entityAssociation->getTextProperties()[1]->setSystemType($systemTypeExample);
                $entityAssociation->getTextProperties()[1]->setNamespaceForVersion($this->getUser()->getCurrentOngoingNamespace());
                $entityAssociation->getTextProperties()[1]->setEntityAssociation($entityAssociation);
            }

            $em->persist($entityAssociation);
            $em->flush();

            $this->addFlash('success', 'Relation created !');

            return $this->redirectToRoute($object.'_show_with_version', [
                'id' => $objectId,
                'namespaceFromUrlID' => $objectNamespaceId,
                '_fragment' => 'relations'
            ]);
        }

        return $this->render('entityAssociation/new.html.twig', array(
            'object' => $object,
            'source' => $source,
            'entityAssociationForm' => $form->createView()
        ));
    }

    /**
     * @Route("/entity-association/{id}", name="entity_association_show", requirements={"id"="^[0-9]+$"})
     * @Route("/entity-association/{id}/inverse", name="entity_association_inverse_show", requirements={"id"="^[0-9]+$"})
     * @param EntityAssociation $entityAssociation
     * @return Response the rendered template
     */
    public function showAction(Request $request, EntityAssociation $entityAssociation)
    {
        $inverse = false;
        if($request->attributes->get('_route') == 'entity_association_inverse_show'){
            $inverse = true;
        }

        return $this->render('entityAssociation/show.html.twig', array(
            'entityAssociation' => $entityAssociation,
            'inverse' => $inverse
        ));
    }

    /**
     * @Route("/entity-association/{id}/edit", name="entity_association_edit", requirements={"id"="^[0-9]+$"})
     * @Route("/entity-association/{id}/inverse/edit", name="entity_association_inverse_edit", requirements={"id"="^[0-9]+$"})
     */
    public function editAction(Request $request, EntityAssociation $entityAssociation, ClassRepository $classRepository, ClassVersionRepository $classVersionRepository, PropertyRepository $propertyRepository, PropertyVersionRepository $propertyVersionRepository)
    {
        $inverse = false;
        if($request->attributes->get('_route') == 'entity_association_inverse_edit'){
            $inverse = true;
        }

        $em = $this->doctrine->getManager();

        if($entityAssociation->getSourceObjectType() == 'class' and !$inverse)
        {
            $firstEntity = $classRepository->find($entityAssociation->getSourceClass()->getId());
            if (!$firstEntity) {
                throw $this->createNotFoundException('The class n° '.$entityAssociation->getSourceClass()->getId().' does not exist');
            }
            $namespaceForEntityVersion = $firstEntity->getClassVersionForDisplay()->getNamespaceForVersion();
        }
        elseif($entityAssociation->getSourceObjectType() == 'property' and !$inverse)
        {
            $firstEntity = $propertyRepository->find($entityAssociation->getSourceProperty()->getId());
            if (!$firstEntity) {
                throw $this->createNotFoundException('The property n° '.$entityAssociation->getSourceProperty()->getId().' does not exist');
            }
            $namespaceForEntityVersion = $firstEntity->getPropertyVersionForDisplay()->getNamespaceForVersion();
        }
        elseif($entityAssociation->getTargetObjectType() == 'class' and $inverse)
        {
            $firstEntity = $classRepository->find($entityAssociation->getTargetClass()->getId());
            if (!$firstEntity) {
                throw $this->createNotFoundException('The class n° '.$entityAssociation->getTargetClass()->getId().' does not exist');
            }
            $namespaceForEntityVersion = $firstEntity->getClassVersionForDisplay()->getNamespaceForVersion();
        }
        elseif($entityAssociation->getTargetObjectType() == 'property' and $inverse)
        {
            $firstEntity = $propertyRepository->find($entityAssociation->getTargetProperty()->getId());
            if (!$firstEntity) {
                throw $this->createNotFoundException('The property n° '.$entityAssociation->getTargetProperty()->getId().' does not exist');
            }
            $namespaceForEntityVersion = $firstEntity->getPropertyVersionForDisplay()->getNamespaceForVersion();
        }

        $this->denyAccessUnlessGranted('edit', $entityAssociation);

        // FILTRAGE
        $namespacesId[] = $this->getUser()->getCurrentOngoingNamespace()->getId();

        // Sans oublier les namespaces références si indisponibles
        foreach($this->getUser()->getCurrentOngoingNamespace()->getReferencedNamespaceAssociations() as $referencedNamespacesAssociation){
            if(!in_array($referencedNamespacesAssociation->getReferencedNamespace()->getId(), $namespacesId)){
                $namespacesId[] = $referencedNamespacesAssociation->getReferencedNamespace()->getId();
            }
        }

        if($entityAssociation->getSourceObjectType() == "class"){
            $arrayEntitiesVersion = $classVersionRepository->findIdAndStandardLabelOfClassesVersionByNamespacesId($namespacesId);
        }
        elseif($entityAssociation->getSourceObjectType() == "property"){
            $arrayEntitiesVersion = $propertyVersionRepository->findIdAndStandardLabelOfPropertiesVersionByNamespacesId($namespacesId);
        }

        $form = $this->createForm(EntityAssociationEditForm::class, $entityAssociation, array(
            'object' => $entityAssociation->getSourceObjectType(),
            'inverse' => $inverse,
            'entitiesVersion' => $arrayEntitiesVersion,
            'defaultSource' => $entityAssociation->getSource()->getId(),
            'defaultTarget' => $entityAssociation->getTarget()->getId()
        ));

        // only handles data on POST
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($entityAssociation->getTargetObjectType() == 'class' and $inverse){
                $sourceClass = $classRepository->find($form->get("sourceClassVersion")->getData());
                $entityAssociation->setSourceClass($sourceClass);
                $sourceNamespace = $classVersionRepository->findClassVersionByClassAndNamespacesId($sourceClass, $namespacesId)->getNamespaceForVersion();
                $entityAssociation->setSourceNamespaceForVersion($sourceNamespace);
            }
            elseif($entityAssociation->getTargetObjectType() == 'class' and !$inverse){
                $targetClass = $classRepository->find($form->get("targetClassVersion")->getData());
                $entityAssociation->setTargetClass($targetClass);
                $targetNamespace = $classVersionRepository->findClassVersionByClassAndNamespacesId($targetClass, $namespacesId)->getNamespaceForVersion();
                $entityAssociation->setTargetNamespaceForVersion($targetNamespace);
            }
            elseif($entityAssociation->getTargetObjectType() == 'property' and $inverse){
                $sourceProperty = $propertyRepository->find($form->get("sourcePropertyVersion")->getData());
                $entityAssociation->setSourceProperty($sourceProperty);
                $sourceNamespace = $propertyVersionRepository->findPropertyVersionByPropertyAndNamespacesId($sourceProperty, $namespacesId)->getNamespaceForVersion();
                $entityAssociation->setSourceNamespaceForVersion($sourceNamespace);
            }
            elseif($entityAssociation->getTargetObjectType() == 'property' and !$inverse){
                $targetProperty = $propertyRepository->find($form->get("targetPropertyVersion")->getData());
                $entityAssociation->setTargetProperty($targetProperty);
                $targetNamespace = $propertyVersionRepository->findPropertyVersionByPropertyAndNamespacesId($targetProperty, $namespacesId)->getNamespaceForVersion();
                $entityAssociation->setTargetNamespaceForVersion($targetNamespace);
            }

            $entityAssociation = $form->getData();
            $entityAssociation->setModifier($this->getUser());
            $entityAssociation->setModificationTime(new \DateTime('now'));

            $em = $this->doctrine->getManager();
            $em->persist($entityAssociation);
            $em->flush();

            $this->addFlash('success', 'Relation edited !');

            if(!$inverse){
                return $this->redirectToRoute($entityAssociation->getSourceObjectType().'_show', [
                    'id' => $entityAssociation->getSource()->getId(),
                    '_fragment' => 'relations'
                ]);
            }
            else{
                return $this->redirectToRoute($entityAssociation->getTargetObjectType().'_show', [
                    'id' => $entityAssociation->getTarget()->getId(),
                    '_fragment' => 'relations'
                ]);
            }
        }

        return $this->render('entityAssociation/edit.html.twig', array(
            'entityAssociation' => $entityAssociation,
            'inverse' => $inverse,
            'entityAssociationForm' => $form->createView(),
        ));
    }

    /**
     * @Route("/entity-association/{id}/edit-validity/{validationStatus}", name="entity_association_validation_status_edit", requirements={"id"="^[0-9]+$", "validationStatus"="^(26|27|28|37){1}$"})
     * @param EntityAssociation $entityAssociation
     * @param SystemType $validationStatus
     * @param Request $request
     * @throws \Exception in case of unsuccessful validation
     * @return RedirectResponse|Response
     */
    public function editValidationStatusAction(EntityAssociation $entityAssociation, SystemType $validationStatus, Request $request)
    {
        // On doit avoir une version de l'association sinon on lance une exception.
        if(is_null($entityAssociation)){
            throw $this->createNotFoundException('The entity association n°'.$entityAssociation->getId().' does not exist. Please contact an administrator.');
        }

        //Denied access if not an authorized validator
        $this->denyAccessUnlessGranted('validate', $entityAssociation->getNamespaceForVersion());

        //Verifier que les références sont cohérents
        $nsRefsEntityAssociation = $entityAssociation->getNamespaceForVersion()->getAllReferencedNamespaces();
        $nsRefsEntityAssociation->add($entityAssociation->getNamespaceForVersion());
        $nsSource = $entityAssociation->getSourceNamespaceForVersion();
        $nsTarget = $entityAssociation->getTargetNamespaceForVersion();
        if(!$nsRefsEntityAssociation->contains($nsSource) || !$nsRefsEntityAssociation->contains($nsTarget)){
            $uriNamespaceMismatches = $this->generateUrl('namespace_show', ['id' => $entityAssociation->getNamespaceForVersion()->getId(), '_fragment' => 'mismatches']);
            $this->addFlash('warning', 'This relation can\'t be validated. Check <a href="'.$uriNamespaceMismatches.'">mismatches</a>.');
            return $this->redirectToRoute('entity_association_show', [
                'id' => $entityAssociation->getId()
            ]);
        }

        $entityAssociation->setModifier($this->getUser());

        $newValidationStatus = new SystemType();

        try{
            $em = $this->doctrine->getManager();
            $newValidationStatus = $em->getRepository(SystemType::class)
                ->findOneBy(array('id' => $validationStatus->getId()));
        } catch (\Exception $e) {
            throw new BadRequestHttpException('The provided status does not exist.');
        }

        if (!is_null($newValidationStatus)) {
            $statusId = intval($newValidationStatus->getId());
            if (in_array($statusId, [26,27,28,37], true)) {
                $entityAssociation->setValidationStatus($newValidationStatus);
                $entityAssociation->setModifier($this->getUser());
                $entityAssociation->setModificationTime(new \DateTime('now'));

                $em->persist($entityAssociation);

                $em->flush();

                if ($statusId == 27){
                    return $this->redirectToRoute('entity_association_edit', [
                        'id' => $entityAssociation->getId()
                    ]);
                }
                else return $this->redirectToRoute('entity_association_show', [
                    'id' => $entityAssociation->getId()
                ]);

            }
        }

        return $this->redirectToRoute('entity_association_show', [
            'id' => $entityAssociation->getId()
        ]);
    }

    /**
     * @Route("/entity-association/{id}/delete", name="entity_association_delete", requirements={"id"="^([0-9]+)|(associationId){1}$"})
     */
    public function deleteAction(Request $request, EntityAssociation $entityAssociation)
    {
        $this->denyAccessUnlessGranted('delete', $entityAssociation);

        $em = $this->doctrine->getManager();
        foreach($entityAssociation->getTextProperties() as $textProperty)
        {
            $em->remove($textProperty);
        }
        foreach($entityAssociation->getComments() as $comment)
        {
            $em->remove($comment);
        }
        $em->remove($entityAssociation);
        $em->flush();
        return new JsonResponse(null, 204);
    }
}