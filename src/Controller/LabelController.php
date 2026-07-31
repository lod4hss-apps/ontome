<?php
/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 03/07/2018
 * Time: 10:56
 */

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Entity\Label;
use App\Entity\OntoClass;
use App\Entity\Property;
use App\Entity\SystemType;
use App\Form\LabelForm;
use App\Repository\ClassVersionRepository;
use App\Repository\NamespaceRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProjectRepository;
use App\Repository\PropertyVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Persistence\ManagerRegistry;

class LabelController  extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        // Inject the ManagerRegistry into the controller
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/label/{id}", name="label_show", requirements={"id"="^[0-9]+"})
     * @param string $id
     * @return Response the rendered template
     */
    public function showAction(Label $label, LoggerInterface $logger)
    {
        $logger->info('Showing text property: ' . $label->getId());
        return $this->render('label/show.html.twig', array(
            'label' => $label
        ));
    }

    /**
     * @Route("/label/{id}/edit", name="label_edit", requirements={"id"="^[0-9]+"})
     */
    public function editAction(Label $label, Request $request, ProfileRepository $profileRepository, ProjectRepository $projectRepository, NamespaceRepository $namespaceRepository)
    {
        $canInverseLabel = false;
        $allEntities = null;
        $em = $this->doctrine->getManager();

        if(!is_null($label->getClass())){
            $object = $label->getClass();
            $redirectToRoute = 'class_edit';
            $redirectToRouteFragment = 'identification';
        }
        else if(!is_null($label->getProperty())){
            $object = $label->getProperty();
            $redirectToRoute = 'property_edit';
            $redirectToRouteFragment = 'identification';
            $canInverseLabel = true;
        }
        else if(!is_null($label->getProfile())){
            $object = $label->getProfile();
            $redirectToRoute = 'profile_edit';
            $redirectToRouteFragment = 'identification';
            $t = 'profile';
            $allEntities = $profileRepository->findAll();
            $allEntities = array_filter($allEntities, function($v) use ($label){ return $v != $label->getProfile();});
        }
        else if(!is_null($label->getProject())){
            $object = $label->getProject();
            $redirectToRoute = 'project_edit';
            $redirectToRouteFragment = 'identification';
            $t = 'project';
            $allEntities = $projectRepository->findAll();
            $allEntities = array_filter($allEntities, function($v) use ($label){ return $v != $label->getProject();});
        }
        else if(!is_null($label->getNamespace())){
            $object = $label->getNamespace();
            if($label->getNamespace()->getIsOngoing()){
                $label->setLabel(str_replace(' ongoing', '', $label->getLabel()));
            }
            $redirectToRoute = 'namespace_edit';
            $redirectToRouteFragment = 'identification';
            $t = 'namespace';
            $allEntities = $namespaceRepository->findAll();
            $allEntities = array_filter($allEntities, function($v) use ($label){ return $v != $label->getNamespace();});
        }
        else throw $this->createNotFoundException('The related object for the label n° '.$label->getId().' does not exist. Please contact an administrator.');

        if($object instanceof OntoClass){
            $this->denyAccessUnlessGranted('edit', $object->getClassVersionForDisplay());
        }
        elseif($object instanceof Property){
            $this->denyAccessUnlessGranted('edit', $object->getPropertyVersionForDisplay());
        }
        else{
            $this->denyAccessUnlessGranted('edit', $object);
        }

        $label->setModifier($this->getUser());

        $form = $this->createForm(LabelForm::class, $label, ['canInverseLabel' => $canInverseLabel]);

        $form->handleRequest($request);


        //Vérification si le label n'a jamais été utilisé ailleurs pour Profile, Project et Namespace
        $isLabelValid = true;
        if(!is_null($allEntities)){
            $allLabels = new ArrayCollection();
            foreach ($allEntities as $var_entity){
                foreach ($var_entity->getLabels() as $var_label){
                    $allLabels->add($var_label->getLabel());
                }
            }
            if($form->isSubmitted()){
                $flabel = $form->get('label');
                if($allLabels->contains($flabel->getData())){
                    $flabel->addError(new FormError('This label is already used by another ' . $t . ', please enter a different one.'));
                    $isLabelValid = false;
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid() && $isLabelValid) {
            $em = $this->doctrine->getManager();
            if(!is_null($label->getNamespace()) && $label->getNamespace()->getIsOngoing()){
                $label->setLabel($label->getLabel().' ongoing');
            }
            $label->setModifier($this->getUser());
            $em->persist($label);
            $em->flush();

            $this->addFlash('success', 'Label updated!');

            return $this->redirectToRoute($redirectToRoute, [
                'id' => $object->getId(),
                '_fragment' => $redirectToRouteFragment
            ]);
        }

        //If validation status is in validation request or is validation, we can't allow edition of the entity and we rended the show template
        if (!is_null($label->getValidationStatus()) && ($label->getValidationStatus()->getId() === 26 || $label->getValidationStatus()->getId() === 28)) {
            return $this->render('label/show.html.twig', [
                'label' => $label
            ]);
        }

        return $this->render('label/edit.html.twig', [
            'labelForm' => $form->createView(),
            'associatedObject' => $object,
            'label' => $label,
            'canInverseLabel' => $canInverseLabel
        ]);
    }

    /**
     * @Route("/label/new/{object}/{objectId}", name="label_new", requirements={"object"="^(class|property|profile|project|namespace){1}$","objectId"="^[0-9]+"})
     */
    public function newAction($object, $objectId, Request $request, ClassVersionRepository $classVersionRepository, PropertyVersionRepository $propertyVersionRepository, ProfileRepository $profileRepository, ProjectRepository $projectRepository, NamespaceRepository $namespaceRepository)
    {
        $em = $this->doctrine->getManager();

        $label = new Label();
        $canInverseLabel = false;

        if($object === 'class') {
            // Récupérer la version ongoing de la classe identifiée par objectId
            $associatedEntity = $classVersionRepository->findOngoingVersion($objectId);

            if (!$associatedEntity) {
                throw $this->createNotFoundException('The ongoing version of class n° '.$objectId.' does not exist');
            }

            $label->setClass($associatedEntity->getClass());
            $label->setNamespaceForVersion($associatedEntity->getNamespaceForVersion());
            $associatedObject = $associatedEntity;
            $redirectToRoute = 'class_edit';
            $redirectToRouteFragment = 'identification';
        }
        else if($object === 'property') {
            // Récupérer la version ongoing de la propriété identifiée par objectId
            $associatedEntity = $propertyVersionRepository->findOngoingVersion($objectId);

            if (!$associatedEntity) {
                throw $this->createNotFoundException('The ongoing version of property n° '.$objectId.' does not exist');
            }

            $label->setProperty($associatedEntity->getProperty());
            $label->setNamespaceForVersion($associatedEntity->getNamespaceForVersion());
            $associatedObject = $associatedEntity;
            $redirectToRoute = 'property_edit';
            $redirectToRouteFragment = 'identification';
            $canInverseLabel = true;
        }
        else if($object === 'profile') {
            $associatedEntity = $profileRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The profile n° '.$objectId.' does not exist');
            }
            $label->setProfile($associatedEntity);
            $associatedObject = $associatedEntity;
            $redirectToRoute = 'profile_edit';
            $redirectToRouteFragment = 'identification';
        }
        else if($object === 'project') {
            $associatedEntity = $projectRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The project n° '.$objectId.' does not exist');
            }
            $label->setProject($associatedEntity);
            $associatedObject = $associatedEntity;
            $redirectToRoute = 'project_edit';
            $redirectToRouteFragment = 'identification';
        }
        else if($object === 'namespace') {
            $associatedEntity = $namespaceRepository->find($objectId);
            if (!$associatedEntity) {
                throw $this->createNotFoundException('The namespace n° '.$objectId.' does not exist');
            }
            $label->setNamespace($associatedEntity);
            $associatedObject = $associatedEntity;
            $redirectToRoute = 'namespace_edit';
            $redirectToRouteFragment = 'identification';
        }
        else throw $this->createNotFoundException('The requested object "'.$object.'" does not exist!');

        $this->denyAccessUnlessGranted('edit', $associatedObject);

        //ongoingNamespace associated to the label for any kind of object, except Project or Profile
        if($object !== 'project' && $object !== 'profile'  && $object !== 'namespace') {
            $label->setNamespaceForVersion($this->getUser()->getCurrentOngoingNamespace());
        }

        $label->setCreator($this->getUser());
        $label->setModifier($this->getUser());
        $label->setCreationTime(new \DateTime('now'));
        $label->setModificationTime(new \DateTime('now'));


        $form = $this->createForm(LabelForm::class, $label, ['canInverseLabel' => $canInverseLabel]);

        // only handles data on POST
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $label = $form->getData();

            //ongoingNamespace associated to the label for any kind of object, except Project or Profile
            if($object !== 'project' && $object !== 'profile' && $object !== 'namespace') {
                $label->setNamespaceForVersion($this->getUser()->getCurrentOngoingNamespace());
            }

            $label->setCreator($this->getUser());
            $label->setModifier($this->getUser());
            $label->setCreationTime(new \DateTime('now'));
            $label->setModificationTime(new \DateTime('now'));

            $em->persist($label);
            $em->flush();

            $this->addFlash('success', 'Label created!');

            return $this->redirectToRoute($redirectToRoute, [
                'id' => $objectId,
                '_fragment' => $redirectToRouteFragment
            ]);

        }

        return $this->render('label/new.html.twig', [
            'label' => $label,
            'labelForm' => $form->createView(),
            'canInverseLabel' => $canInverseLabel
        ]);
    }

    /**
     * @Route("/label/{id}/edit-validity/{validationStatus}", name="label_validation_status_edit", requirements={"id"="^[0-9]+$", "validationStatus"="^(26|27|28|37){1}$"})
     * @param Label $label
     * @param SystemType $validationStatus
     * @param Request $request
     * @throws \Exception in case of unsuccessful association
     * @return RedirectResponse
     */
    public function editValidationStatusAction(Label $label, SystemType $validationStatus, Request $request)
    {
        $object = null;
        if(!is_null($label->getClass())){
            $object = $label->getClass();
        }
        else if(!is_null($label->getProperty())){
            $object = $label->getProperty();
        }
        else if(!is_null($label->getProject())){
            $object = $label->getProject();
        }
        else if(!is_null($label->getProfile())){
            $object = $label->getProfile();
        }
        else if(!is_null($label->getNamespace())){
            $object = $label->getNamespace();
        }
        else throw $this->createNotFoundException('The related object for the text property  n° '.$label->getId().' does not exist. Please contact an administrator.');

        if(!is_null($label->getClass())){
            $this->denyAccessUnlessGranted('validate', $object->getClassVersionForDisplay());
        }
        else if(!is_null($label->getProperty())){
            $this->denyAccessUnlessGranted('validate', $object->getPropertyVersionForDisplay());
        }
        else if(!is_null($label->getNamespace())){
            $this->denyAccessUnlessGranted('validate', $object);
        }
        else{
            throw new AccessDeniedHttpException('The validation of this resource is forbidden.');
        }

        $label->setModifier($this->getUser());

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
                $label->setValidationStatus($newValidationStatus);
                $label->setModifier($this->getUser());
                $label->setModificationTime(new \DateTime('now'));

                $em->persist($label);

                //if the status is not validated, then unvalidate the related class or property if necessary
                if ($statusId != 26) {
                    if (!is_null($label->getClass())){
                        $cv = $object->getClassVersionForDisplay();
                        if (!is_null($cv->getValidationStatus())) {
                            if ($cv->getValidationStatus()->getId() != 27) {
                                $underRevisionStatus = $em->getRepository(SystemType::class)
                                    ->findOneBy(array('id' => 37));
                                $cv->setValidationStatus($underRevisionStatus);
                            }
                        }
                        else $cv->setValidationStatus(null);
                        $em->persist($cv);
                    }
                    else if (!is_null($label->getProperty())){
                        $pv = $object->getPropertyVersionForDisplay();
                        if (!is_null($pv->getValidationStatus())) {
                            if ($pv->getValidationStatus()->getId() != 27) {
                                $underRevisionStatus = $em->getRepository(SystemType::class)
                                    ->findOneBy(array('id' => 37));
                                $pv->setValidationStatus($underRevisionStatus);
                            }
                        }
                        else $pv->setValidationStatus(null);
                        $em->persist($pv);
                    }
                }

                $em->flush();

                if ($statusId == 27){
                    return $this->redirectToRoute('label_edit', [
                        'id' => $label->getId()
                    ]);
                }
                else return $this->redirectToRoute('label_show', [
                    'id' => $label->getId()
                ]);

            }
        }

        return $this->redirectToRoute('label_show', [
            'id' => $label->getId()
        ]);
    }
}