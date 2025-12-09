<?php
/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 28/06/2017
 * Time: 15:50
 */

namespace AppBundle\Controller;

use AppBundle\Entity\ClassAssociation;
use AppBundle\Entity\EntityAssociation;
use AppBundle\Entity\Label;
use AppBundle\Entity\OntoClass;
use AppBundle\Entity\OntoClassVersion;
use AppBundle\Entity\OntoNamespace;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectAssociation;
use AppBundle\Entity\Property;
use AppBundle\Entity\PropertyAssociation;
use AppBundle\Entity\PropertyVersion;
use AppBundle\Entity\ReferencedNamespaceAssociation;
use AppBundle\Entity\TextProperty;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProjectAssociation;
use AppBundle\Form\ImportNamespaceForm;
use AppBundle\Form\ProjectQuickAddForm;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ProjectController  extends Controller
{
    /**
     * @Route("/project")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $projects = $em->getRepository('AppBundle:Project')
            ->findAll();

        return $this->render('project/list.html.twig', [
            'projects' => $projects
        ]);
    }

    /**
     * @Route("/project/new", name="project_new_user")
     */
    public function newUserProjectAction(Request $request)
    {

        $tokenInterface = $this->get('security.token_storage')->getToken();
        $isAuthenticated = $tokenInterface->isAuthenticated();
        if (!$isAuthenticated)
            throw new AccessDeniedException('You must be an authenticated user to access this page.');

        $project = new Project();

        $em = $this->getDoctrine()->getManager();
        $systemTypeDescription = $em->getRepository('AppBundle:SystemType')->find(16); //systemType 16 = Description

        $description = new TextProperty();
        $description->setProject($project);
        $description->setSystemType($systemTypeDescription);
        $description->setCreator($this->getUser());
        $description->setModifier($this->getUser());
        $description->setCreationTime(new \DateTime('now'));
        $description->setModificationTime(new \DateTime('now'));

        $project->addTextProperty($description);

        $userProjectAssociation = new UserProjectAssociation();

        $now = new \DateTime();

        $project->setCreator($this->getUser());
        $project->setModifier($this->getUser());

        $projectLabel = new Label();
        $projectLabel->setProject($project);
        $projectLabel->setIsStandardLabelForLanguage(true);
        $projectLabel->setCreator($this->getUser());
        $projectLabel->setModifier($this->getUser());
        $projectLabel->setCreationTime(new \DateTime('now'));
        $projectLabel->setModificationTime(new \DateTime('now'));

        $project->addLabel($projectLabel);

        $allProjects = $em->getRepository('AppBundle:Project')->findAll();

        $allLabels = new ArrayCollection();
        foreach ($allProjects as $var_project){
            foreach ($var_project->getLabels() as $label){
                $allLabels->add($label->getLabel());
            }
        }

        $form = $this->createForm(ProjectQuickAddForm::class, $project);
        // only handles data on POST
        $form->handleRequest($request);

        //Vérification si le label n'a jamais été utilisé ailleurs
        $isLabelValid = true;
        if($form->isSubmitted()){
            $labels = $form->get('labels');
            foreach ($labels as $label){
                if($allLabels->contains($label->get('label')->getData())){
                    $label->get('label')->addError(new FormError('This label is already used by another project, please enter a different one.'));
                    $isLabelValid = false;
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid() && $isLabelValid) {
            $project = $form->getData();
            $project->setStartDate($now);
            $project->setCreator($this->getUser());
            $project->setModifier($this->getUser());
            $project->setCreationTime(new \DateTime('now'));
            $project->setModificationTime(new \DateTime('now'));

            $userProjectAssociation->setUser($this->getUser());
            $userProjectAssociation->setProject($project);
            $userProjectAssociation->setPermission(1);
            $userProjectAssociation->setNotes('Project created by user via OntoME form.');
            $userProjectAssociation->setStartDate($now);
            $userProjectAssociation->setCreator($this->getUser());
            $userProjectAssociation->setModifier($this->getUser());
            $userProjectAssociation->setCreationTime(new \DateTime('now'));
            $userProjectAssociation->setModificationTime(new \DateTime('now'));

            $this->getUser()->setCurrentActiveProject($project);

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->persist($userProjectAssociation);
            $em->persist($this->getUser());
            $em->flush();

            return $this->redirectToRoute('user_show', [
                'id' =>$userProjectAssociation->getUser()->getId()
            ]);

        }

        $em = $this->getDoctrine()->getManager();


        return $this->render('project/new.html.twig', [
            'errors' => $form->getErrors(),
            'project' => $project,
            'projectForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{id}", name="project_show", requirements={"id"="^[0-9]+$"})
     * @param string $id
     * @return Response the rendered template
     */
    public function showAction(Project $project)
    {
        $em = $this->getDoctrine()->getManager();

        $associatedNamespacesForAPIProject = $em->getRepository('AppBundle:OntoNamespace')
            ->findApiNamespacesProject($project);

        return $this->render('project/show.html.twig', array(
            'project' => $project,
            'associatedNamespacesForAPIProject' => $associatedNamespacesForAPIProject
        ));
    }

    /**
     * @Route("/project/{id}/edit", name="project_edit", requirements={"id"="^[0-9]+$"})
     * @param Project $project
     * @return Response the rendered template
     */
    public function editAction(Project $project, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:User')
            ->findAllNotInProject($project);

        $namespacesPublicProject = $em->getRepository('AppBundle:OntoNamespace')
            ->findNamespacesInPublicProject();

        $associatedNamespacesForAPIProject = $em->getRepository('AppBundle:OntoNamespace')
            ->findApiNamespacesProject($project);

        // On crée le formulaire d'importation XML
        $formImport = $this->createForm(ImportNamespaceForm::class);

        // On capture la requête pour l'import
        $formImport->handleRequest($request);

        // Route pour retour, réutilisable dans tout le code ci-dessous
        $redirectRoute = $this->redirectToRoute('project_edit', [
            'id' => $project->getId(),
            '_fragment' => 'import'
        ]);

        if ($formImport->isSubmitted() && $formImport->isValid()) {

            // On récupère le fichier XML
            $file = $formImport['uploadXMLFile']->getData();

            // On vérifie si le fichier n'est pas vide
            if (is_null($file)) {
                $this->addFlash('error', 'Please select an XML file to upload before submitting the form.');
                return $redirectRoute;
            }

            if ($file->getClientMimeType() == "text/xml") {
                // Convertit le fichier XML en un objet PHP
                $nodeXmlNamespace = @simplexml_load_file($file->getPathname());
                if ($nodeXmlNamespace !== false) {

                    // On va vérifier la validité du XML avec le schéma XSD
                    $dom = new \DOMDocument();
                    $dom->loadXML($nodeXmlNamespace->asXML());

                    // Import XSD
                    $pathXMLSchema = "../web/documents/schemaImportXmlwithReferences.xml";
                    $simpleXMLElementSchema = @simplexml_load_file($pathXMLSchema);

                    // On vérifie si le XML est valide avec notre schéma XSD
                    try {
                        // S'il est pas valide une exception est lancée
                        if (!$dom->schemaValidateSource($simpleXMLElementSchema->asXML())) {
                            $this->addFlash('error', 'A problem occurred while validating the XML file against the schema.');
                            return $redirectRoute;
                        }
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        $this->addFlash('error', 'XML is not valid against the schema. See <a href="https://github.com/lod4hss-apps/ontome/wiki/Import-des-namespaces-XML">here</a>.<br><span class="small text-muted">' . $errorMessage . '</span>');
                        return $redirectRoute;
                    }

                    // Vérifier si le namespace root existe sinon on arrête tout.
                    $namespaceRoot = $project->getManagedNamespaces()
                        ->filter(function ($v) {
                            return $v->getIsTopLevelNamespace();
                        })->first();
                    if (!$namespaceRoot) {
                        $this->addFlash('error', "You must create a root namespace for this project before importing namespaces.");
                        return $redirectRoute;
                    }

                    // On prépare les system types nécessaires
                    $systemTypeScopeNote = $em->getRepository('AppBundle:SystemType')->find(1); //systemType 1 = scope note
                    $systemTypeExample = $em->getRepository('AppBundle:SystemType')->find(7); // example
                    $systemTypeVersion = $em->getRepository('AppBundle:SystemType')->find(31); //owl:versionInfo
                    $systemTypeContributors = $em->getRepository('AppBundle:SystemType')->find(2); //contributor
                    $systemTypeDescription = $em->getRepository('AppBundle:SystemType')->find(16); //description
                    $systemTypeContextNote = $em->getRepository('AppBundle:SystemType')->find(34); //context note
                    $systemTypeBibliographicalNote = $em->getRepository('AppBundle:SystemType')->find(35); //bibliographical note

                    // Nouveau namespace version
                    $newNamespaceVersion = new OntoNamespace();
                    $newNamespaceVersion->setTopLevelNamespace($namespaceRoot);

                    // Le fichier semble valide, on peut maintenant vérifier la cohérence des métadonnées du namespaces

                    // Label
                    // Scope note: un par langue
                    $langCollection = new ArrayCollection();
                    $defaultStandardLabel = null;
                    foreach ($nodeXmlNamespace->standardLabel as $keySl => $nodeXmlStandardLabel) {
                        if (!$langCollection->contains((string) $nodeXmlStandardLabel->attributes()->lang)) {
                            $langCollection->add((string) $nodeXmlStandardLabel->attributes()->lang);
                        } else {
                            $this->addFlash('error', "Two namespace standard labels have the same language. This is not allowed - each language can only have one standard label.<br>Problematic language: " . (string) $nodeXmlStandardLabel->attributes()->lang);
                            return $redirectRoute;
                        }
                        $namespaceLabel = new Label();
                        $namespaceLabel->setIsStandardLabelForLanguage(true);
                        $namespaceLabel->setLabel((string) $nodeXmlStandardLabel);
                        $namespaceLabel->setLanguageIsoCode((string) $nodeXmlStandardLabel->attributes()->lang);
                        $namespaceLabel->setCreator($this->getUser());
                        $namespaceLabel->setModifier($this->getUser());
                        $namespaceLabel->setCreationTime(new \DateTime('now'));
                        $namespaceLabel->setModificationTime(new \DateTime('now'));
                        $newNamespaceVersion->addLabel($namespaceLabel);
                        $em->persist($namespaceLabel);

                        if ($namespaceLabel->getLanguageIsoCode() == "en" || (is_null($defaultStandardLabel) && $namespaceLabel->getLanguageIsoCode() == "fr")) {
                            $defaultStandardLabel = (string) $nodeXmlStandardLabel;
                        }
                    }

                    // StandardLabel
                    if (is_null($defaultStandardLabel)) {
                        $newNamespaceVersion->setStandardLabel((string) $nodeXmlNamespace->standardLabel);
                    } else {
                        $newNamespaceVersion->setStandardLabel($defaultStandardLabel);
                    }

                    // Description : un par langue
                    $langCollection = new ArrayCollection();
                    foreach ($nodeXmlNamespace->description as $keyD => $nodeXmlDescription) {
                        if (!$langCollection->contains((string) $nodeXmlDescription->attributes()->lang)) {
                            $langCollection->add((string) $nodeXmlDescription->attributes()->lang);
                        } else {
                            $this->addFlash('error', "Two namespace descriptions have the same language. This is not allowed - each language can only have one description.<br>Problematic language: " . (string) $nodeXmlDescription->attributes()->lang);
                            return $redirectRoute;
                        }
                        $namespaceDescription = new TextProperty();
                        $namespaceDescription->setTextProperty((string) $nodeXmlDescription);
                        $namespaceDescription->setSystemType($systemTypeDescription);
                        $namespaceDescription->setLanguageIsoCode((string) $nodeXmlDescription->attributes()->lang);
                        $namespaceDescription->setCreator($this->getUser());
                        $namespaceDescription->setModifier($this->getUser());
                        $namespaceDescription->setCreationTime(new \DateTime('now'));
                        $namespaceDescription->setModificationTime(new \DateTime('now'));
                        $newNamespaceVersion->addTextProperty($namespaceDescription);
                        $em->persist($namespaceDescription);
                    }

                    // Version
                    $txtpVersion = new TextProperty();
                    $txtpVersion->setTextProperty((string) $nodeXmlNamespace->version);
                    $txtpVersion->setSystemType($systemTypeVersion);
                    $txtpVersion->setCreator($this->getUser());
                    $txtpVersion->setModifier($this->getUser());
                    $txtpVersion->setCreationTime(new \DateTime('now'));
                    $txtpVersion->setModificationTime(new \DateTime('now'));
                    $newNamespaceVersion->addTextProperty($txtpVersion);
                    $em->persist($txtpVersion);

                    // published_at
                    if (!empty((string) $nodeXmlNamespace->publishedAt)) {
                        $newNamespaceVersion->setPublishedAt(new \DateTime((string) $nodeXmlNamespace->publishedAt));
                    } else {
                        $now = new \DateTime('now');
                        $newNamespaceVersion->setPublishedAt($now);
                    }

                    // Contributors
                    if (!empty((string) $nodeXmlNamespace->contributors)) {
                        $txtpContributors = new TextProperty();
                        $txtpContributors->setTextProperty((string) $nodeXmlNamespace->contributors);
                        $txtpContributors->setSystemType($systemTypeContributors);
                        $txtpContributors->setCreator($this->getUser());
                        $txtpContributors->setModifier($this->getUser());
                        $txtpContributors->setCreationTime(new \DateTime('now'));
                        $txtpContributors->setModificationTime(new \DateTime('now'));
                        $newNamespaceVersion->addTextProperty($txtpContributors);
                        $em->persist($txtpContributors);
                    }

                    // Références
                    $idsReferences = new ArrayCollection();
                    foreach ($nodeXmlNamespace->referenceNamespace as $keyRefNs => $nodeXmlReferenceNamespace) {
                        if (!$idsReferences->contains((string) $nodeXmlReferenceNamespace)) {
                            $idsReferences->add((integer) $nodeXmlReferenceNamespace);
                        }
                        $referencedNamespaceAssociation = new ReferencedNamespaceAssociation();
                        $referencedNamespaceAssociation->setNamespace($newNamespaceVersion);
                        $referencedNamespace = $em->getRepository('AppBundle:OntoNamespace')->findOneBy(array("id" => (integer) $nodeXmlReferenceNamespace));

                        // Le namespace référencé doit exister
                        if (is_null($referencedNamespace)) {
                            $this->addFlash('error', "The referenced namespace " . (integer) $nodeXmlReferenceNamespace . " was not found.<br>Problematic reference ID: " . (integer) $nodeXmlReferenceNamespace);
                            return $redirectRoute;
                        }

                        // Le namespace référencé ne peut pas être un namespace root
                        if ($referencedNamespace->getIsTopLevelNamespace()) {
                            $this->addFlash('error', "The namespace " . (integer) $nodeXmlReferenceNamespace . " is root and cannot be used as a reference.<br>Problematic reference ID: " . (integer) $nodeXmlReferenceNamespace);
                            return $redirectRoute;
                        }

                        $referencedNamespaceAssociation->setReferencedNamespace($referencedNamespace);
                        $newNamespaceVersion->addReferencedNamespaceAssociation($referencedNamespaceAssociation);
                        $referencedNamespaceAssociation->setCreator($this->getUser());
                        $referencedNamespaceAssociation->setModifier($this->getUser());
                        $referencedNamespaceAssociation->setCreationTime(new \DateTime('now'));
                        $referencedNamespaceAssociation->setModificationTime(new \DateTime('now'));
                        $em->persist($referencedNamespaceAssociation);
                    }

                    // Les métadonnées semblent correctes, on peut maintenant vérifier les classes et propriétés

                    $nodeXmlClasses = $nodeXmlNamespace->classes;
                    $nodeXmlProperties = $nodeXmlNamespace->properties;

                    // Vérificateurs des identifiersInNamespace pour l'ensemble des classes et propriétés
                    $arrayIdentifiers = new ArrayCollection();

                    foreach ($nodeXmlClasses->children() as $key => $nodeXmlClass) {
                        // Pour vérification de l'unicité des identifiers
                        if (!$arrayIdentifiers->contains((string) $nodeXmlClass->identifierInNamespace)) {
                            $arrayIdentifiers->add((string) $nodeXmlClass->identifierInNamespace);
                        } else {
                            $this->addFlash('error', "At least 2 classes have the same identifier. Each class and property must have a unique identifier within the namespace.<br>Problematic identifier: " . (string) $nodeXmlClass->identifierInNamespace);
                            return $redirectRoute;
                        }
                        // Class
                        $class = null;
                        // Vérifier si la classe n'existe déjà pas dans un des namespaces du root namespace (comparaison par identifierInNamespace)
                        foreach ($namespaceRoot->getChildVersions() as $childNamespace) {
                            foreach ($childNamespace->getClasses() as $tempClass) {
                                if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlClass->identifierInNamespace) {
                                    $class = $tempClass;
                                    break; // Inutile d'aller plus loin la première vraie égalité suffit
                                }
                            }
                        }

                        if (is_null($class)) {
                            // Aucune classe ayant cet identifier: on a donc une nouvelle classe à créer
                            $class = new OntoClass();
                            $class->setIdentifierInNamespace((string) $nodeXmlClass->identifierInNamespace);
                            $class->setIdentifierInURI((string) $nodeXmlClass->identifierInURI);
                            $class->setIsManualIdentifier(is_null($newNamespaceVersion->getTopLevelNamespace()->getClassPrefix()));
                            $class->setCreator($this->getUser());
                            $class->setModifier($this->getUser());
                            $class->setCreationTime(new \DateTime('now'));
                            $class->setModificationTime(new \DateTime('now'));
                            $em->persist($class);
                        }

                        // Class Version
                        $newClassVersion = new OntoClassVersion();
                        $newClassVersion->setClass($class);
                        $newClassVersion->setNamespaceForVersion($newNamespaceVersion);
                        $newClassVersion->setCreator($this->getUser());
                        $newClassVersion->setModifier($this->getUser());
                        $newClassVersion->setCreationTime(new \DateTime('now'));
                        $newClassVersion->setModificationTime(new \DateTime('now'));

                        $class->addClassVersion($newClassVersion);
                        $em->persist($newClassVersion);

                        // Scope note: un par langue
                        $langCollection = new ArrayCollection();
                        if (!is_null($nodeXmlClass->textProperties->scopeNote)) {
                            foreach ($nodeXmlClass->textProperties->scopeNote as $keySn => $nodeXmlScopeNote) {
                                if (!$langCollection->contains((string) $nodeXmlScopeNote->attributes()->lang)) {
                                    $langCollection->add((string) $nodeXmlScopeNote->attributes()->lang);
                                } else {
                                    $this->addFlash('error', "At least 2 scope notes have the same language for class " . $newClassVersion->getClass()->getIdentifierInNamespace() . ". Each scope note must have a unique language.");
                                    return $redirectRoute;
                                }
                                $scopeNote = new TextProperty();
                                $scopeNote->setClass($class);
                                $scopeNote->setNamespaceForVersion($newNamespaceVersion);
                                $scopeNote->setTextProperty((string) $nodeXmlScopeNote);
                                $scopeNote->setLanguageIsoCode((string) $nodeXmlScopeNote->attributes()->lang);
                                $scopeNote->setSystemType($systemTypeScopeNote);
                                $scopeNote->setCreator($this->getUser());
                                $scopeNote->setModifier($this->getUser());
                                $scopeNote->setCreationTime(new \DateTime('now'));
                                $scopeNote->setModificationTime(new \DateTime('now'));

                                $class->addTextProperty($scopeNote);
                                $em->persist($scopeNote);
                            }
                        }

                        // Examples
                        if (!is_null($nodeXmlClass->textProperties->example)) {
                            foreach ($nodeXmlClass->textProperties->example as $keyEx => $nodeXmlExample) {
                                $example = new TextProperty();
                                $example->setClass($class);
                                $example->setNamespaceForVersion($newNamespaceVersion);
                                $example->setTextProperty("<p>" . (string) $nodeXmlExample . "</p>");
                                $example->setLanguageIsoCode((string) $nodeXmlExample->attributes()->lang);
                                $example->setSystemType($systemTypeExample);
                                $example->setCreator($this->getUser());
                                $example->setModifier($this->getUser());
                                $example->setCreationTime(new \DateTime('now'));
                                $example->setModificationTime(new \DateTime('now'));

                                $class->addTextProperty($example);
                                $em->persist($example);
                            }
                        }

                        // Context note
                        if (!is_null($nodeXmlClass->textProperties->contextNote)) {
                            foreach ($nodeXmlClass->textProperties->contextNote as $keyEx => $nodeXmlContextNote) {
                                $contextNote = new TextProperty();
                                $contextNote->setClass($class);
                                $contextNote->setNamespaceForVersion($newNamespaceVersion);
                                $contextNote->setEntityNamespaceForVersion($newNamespaceVersion);
                                $contextNote->setTextProperty("<p>" . (string) $nodeXmlContextNote . "</p>");
                                $contextNote->setLanguageIsoCode((string) $nodeXmlContextNote->attributes()->lang);
                                $contextNote->setSystemType($systemTypeContextNote);
                                $contextNote->setCreator($this->getUser());
                                $contextNote->setModifier($this->getUser());
                                $contextNote->setCreationTime(new \DateTime('now'));
                                $contextNote->setModificationTime(new \DateTime('now'));
                                ;

                                $class->addTextProperty($contextNote);
                                $em->persist($contextNote);
                            }
                        }

                        // Bibliographical note
                        if (!is_null($nodeXmlClass->textProperties->bibliographicalNote)) {
                            foreach ($nodeXmlClass->textProperties->bibliographicalNote as $keyEx => $nodeXmlBibliographicalNote) {
                                $bibliographicalNote = new TextProperty();
                                $bibliographicalNote->setClass($class);
                                $bibliographicalNote->setNamespaceForVersion($newNamespaceVersion);
                                $bibliographicalNote->setEntityNamespaceForVersion($newNamespaceVersion);
                                $bibliographicalNote->setTextProperty("<p>" . (string) $nodeXmlBibliographicalNote . "</p>");
                                $bibliographicalNote->setLanguageIsoCode((string) $nodeXmlBibliographicalNote->attributes()->lang);
                                $bibliographicalNote->setSystemType($systemTypeBibliographicalNote);
                                $bibliographicalNote->setCreator($this->getUser());
                                $bibliographicalNote->setModifier($this->getUser());
                                $bibliographicalNote->setCreationTime(new \DateTime('now'));
                                $bibliographicalNote->setModificationTime(new \DateTime('now'));

                                $class->addTextProperty($bibliographicalNote);
                                $em->persist($bibliographicalNote);
                            }
                        }

                        // Label
                        $langs = new ArrayCollection();
                        $defaultStandardLabelEn = null;
                        $defaultStandardLabelFr = null;
                        $defaultStandardLabel = null;
                        foreach ($nodeXmlClass->standardLabel as $keyLabel => $nodeXmlLabel) {
                            $classLabel = new Label();
                            $classLabel->setClass($class);
                            $classLabel->setNamespaceForVersion($newNamespaceVersion);
                            $classLabel->setLabel((string) $nodeXmlLabel);
                            $classLabel->setLanguageIsoCode((string) $nodeXmlLabel->attributes()->lang);
                            if (!$langs->contains((string) $nodeXmlLabel->attributes()->lang)) {
                                $langs->add((string) $nodeXmlLabel->attributes()->lang);
                                $classLabel->setIsStandardLabelForLanguage(true);
                            } else {
                                $classLabel->setIsStandardLabelForLanguage(false);
                            }
                            $classLabel->setCreator($this->getUser());
                            $classLabel->setModifier($this->getUser());
                            $classLabel->setCreationTime(new \DateTime('now'));
                            $classLabel->setModificationTime(new \DateTime('now'));

                            $class->addLabel($classLabel);
                            $em->persist($classLabel);


                            if (is_null($defaultStandardLabelEn) || $classLabel->getLanguageIsoCode() == "en") {
                                $defaultStandardLabelEn = (string) $nodeXmlLabel;
                            }
                            if (is_null($defaultStandardLabelFr) || $classLabel->getLanguageIsoCode() == "fr") {
                                $defaultStandardLabelFr = (string) $nodeXmlLabel;
                            }
                            if (is_null($defaultStandardLabel)) {
                                $defaultStandardLabel = (string) $nodeXmlLabel;
                            }
                        }
                        if (!is_null($defaultStandardLabelEn)) {
                            $newClassVersion->setStandardLabel($defaultStandardLabelEn);
                        } elseif (!is_null($defaultStandardLabelFr)) {
                            $newClassVersion->setStandardLabel($defaultStandardLabelEn);
                        } else {
                            $newClassVersion->setStandardLabel($defaultStandardLabel);
                        }
                    }

                    foreach ($nodeXmlProperties->children() as $key => $nodeXmlProperty) {
                        if (!$arrayIdentifiers->contains((string) $nodeXmlProperty->identifierInNamespace)) {
                            $arrayIdentifiers->add((string) $nodeXmlProperty->identifierInNamespace);
                        } else {
                            $this->addFlash('error', "At least 2 properties have the same identifier. Each class and property must have a unique identifier within the namespace.<br>Problematic identifier: " . (string) $nodeXmlProperty->identifierInNamespace);
                            return $redirectRoute;
                        }

                        // Property
                        $property = null;
                        // Vérifier si la propriété n'existe déjà pas dans un des namespaces du root namespace (comparaison par identifierInNamespace)
                        foreach ($namespaceRoot->getChildVersions() as $childNamespace) {
                            foreach ($childNamespace->getProperties() as $tempProperty) {
                                if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlProperty->identifierInNamespace) {
                                    $property = $tempProperty;
                                    break; // Inutile d'aller plus loin la première vraie égalité suffit
                                }
                            }
                        }

                        if (is_null($property)) {
                            // On a donc une nouvelle propriété
                            $property = new Property();
                            $property->setIdentifierInNamespace((string) $nodeXmlProperty->identifierInNamespace);
                            $property->setIdentifierInURI((string) $nodeXmlProperty->identifierInURI);
                            $property->setIsManualIdentifier(is_null($newNamespaceVersion->getTopLevelNamespace()->getPropertyPrefix()));
                            $property->setCreator($this->getUser());
                            $property->setModifier($this->getUser());
                            $property->setCreationTime(new \DateTime('now'));
                            $property->setModificationTime(new \DateTime('now'));
                            $em->persist($property);
                        }

                        // Property version
                        $newPropertyVersion = new PropertyVersion();
                        $newPropertyVersion->setProperty($property);
                        $newPropertyVersion->setNamespaceForVersion($newNamespaceVersion);

                        // Quelle version Domain ?
                        $xmlDomainNamespace = $nodeXmlProperty->hasDomain->attributes()->referenceNamespace;
                        //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                        if (!is_null($xmlDomainNamespace)) {
                            $domainNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                ->findOneBy(array("id" => (integer) $xmlDomainNamespace));
                            if (!$idsReferences->contains((integer) $xmlDomainNamespace)) {
                                $this->addFlash('error', "A reference namespace for hasDomain has not been declared with the referenceNamespace tag. Please add it.<br>Problematic domain: " . (string) $nodeXmlProperty->hasDomain . " - reference ID: " . (integer) $xmlDomainNamespace);
                                return $redirectRoute;
                            }
                        } else {
                            $domainNamespace = $newNamespaceVersion;
                        }
                        $newPropertyVersion->setDomainNamespace($domainNamespace);

                        // Trouver la classe
                        $domain = null;
                        foreach ($domainNamespace->getClasses() as $tempClass) {
                            if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlProperty->hasDomain) {
                                $domain = $tempClass;
                                break;
                            }
                        }
                        if (is_null($domain)) {
                            $this->addFlash('error', "Domain " . (string) $nodeXmlProperty->hasDomain . " for property " . (string) $nodeXmlProperty->identifierInNamespace . " was not found.");
                            return $redirectRoute;
                        }
                        $newPropertyVersion->setDomain($domain);

                        // Quelle version Range ?
                        $xmlRangeNamespace = $nodeXmlProperty->hasRange->attributes()->referenceNamespace;
                        //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                        if (!is_null($xmlRangeNamespace)) {
                            $rangeNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                ->findOneBy(array("id" => (integer) $xmlRangeNamespace));
                            if (!$idsReferences->contains((integer) $xmlRangeNamespace)) {
                                $this->addFlash('error', "A reference namespace for hasRange has not been declared with the referenceNamespace tag. Please add it.<br>Problematic range: " . (string) $nodeXmlProperty->hasRange . " - reference ID: " . (integer) $xmlRangeNamespace);
                                return $redirectRoute;
                            }
                        } else {
                            $rangeNamespace = $newNamespaceVersion;
                        }
                        $newPropertyVersion->setRangeNamespace($rangeNamespace);

                        // Trouver la classe
                        $range = null;
                        foreach ($rangeNamespace->getClasses() as $tempClass) {
                            if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlProperty->hasRange) {
                                $range = $tempClass;
                                break;
                            }
                        }
                        if (is_null($range)) {
                            $this->addFlash('error', "Range " . (string) $nodeXmlProperty->hasRange . " for property " . (string) $nodeXmlProperty->identifierInNamespace . " was not found.");
                            return $redirectRoute;
                        }
                        $newPropertyVersion->setRange($range);

                        $domainMinQuantifier = null;
                        // La balise est dans le XML ?
                        if (isset($nodeXmlProperty->domainInstancesMinQuantifier)) {
                            //Inutile de vérifier sa valeur, le schéma XSD l'a déjà fait
                            if ((string) $nodeXmlProperty->domainInstancesMinQuantifier == 'n') {
                                $domainMinQuantifier = -1;
                            } else {
                                $domainMinQuantifier = (integer) $nodeXmlProperty->domainInstancesMinQuantifier;
                            }
                        }
                        $newPropertyVersion->setDomainMinQuantifier($domainMinQuantifier);

                        $domainMaxQuantifier = null;
                        // La balise est dans le XML ?
                        if (isset($nodeXmlProperty->domainInstancesMaxQuantifier)) {
                            //Inutile de vérifier sa valeur, le schéma XSD l'a déjà fait
                            if ((string) $nodeXmlProperty->domainInstancesMaxQuantifier == 'n') {
                                $domainMaxQuantifier = -1;
                            } else {
                                $domainMaxQuantifier = (integer) $nodeXmlProperty->domainInstancesMaxQuantifier;
                            }
                        }
                        $newPropertyVersion->setDomainMaxQuantifier($domainMaxQuantifier);

                        $rangeMinQuantifier = null;
                        // La balise est dans le XML ?
                        if (isset($nodeXmlProperty->rangeInstancesMinQuantifier)) {
                            //Inutile de vérifier sa valeur, le schéma XSD l'a déjà fait
                            if ((string) $nodeXmlProperty->rangeInstancesMinQuantifier == 'n') {
                                $rangeMinQuantifier = -1;
                            } else {
                                $rangeMinQuantifier = (integer) $nodeXmlProperty->rangeInstancesMinQuantifier;
                            }
                        }
                        $newPropertyVersion->setRangeMinQuantifier($rangeMinQuantifier);

                        $rangeMaxQuantifier = null;
                        // La balise est dans le XML ?
                        if (isset($nodeXmlProperty->rangeInstancesMaxQuantifier)) {
                            //Inutile de vérifier sa valeur, le schéma XSD l'a déjà fait
                            if ((string) $nodeXmlProperty->rangeInstancesMaxQuantifier == 'n') {
                                $rangeMaxQuantifier = -1;
                            } else {
                                $rangeMaxQuantifier = (integer) $nodeXmlProperty->rangeInstancesMaxQuantifier;
                            }
                        }
                        $newPropertyVersion->setRangeMaxQuantifier($rangeMaxQuantifier);

                        $newPropertyVersion->setCreator($this->getUser());
                        $newPropertyVersion->setModifier($this->getUser());
                        $newPropertyVersion->setCreationTime(new \DateTime('now'));
                        $newPropertyVersion->setModificationTime(new \DateTime('now'));

                        // Label
                        $langs = new ArrayCollection();
                        $defaultStandardLabelEn = null;
                        $defaultStandardLabelFr = null;
                        $defaultStandardLabel = null;
                        foreach ($nodeXmlProperty->label as $keyLabel => $nodeXmlLabel) {
                            $propertyLabel = new Label();
                            $propertyLabel->setProperty($property);
                            $propertyLabel->setNamespaceForVersion($newNamespaceVersion);
                            $propertyLabel->setLabel((string) $nodeXmlLabel->standardLabel);
                            if (!empty((string) $nodeXmlLabel->inverseLabel)) {
                                $propertyLabel->setInverseLabel((string) $nodeXmlLabel->inverseLabel);
                            }
                            $propertyLabel->setLanguageIsoCode((string) $nodeXmlLabel->attributes()->lang);
                            if (!$langs->contains((string) $nodeXmlLabel->attributes()->lang)) {
                                $langs->add((string) $nodeXmlLabel->attributes()->lang);
                                $propertyLabel->setIsStandardLabelForLanguage(true);
                            } else {
                                $propertyLabel->setIsStandardLabelForLanguage(false);
                            }
                            $propertyLabel->setCreator($this->getUser());
                            $propertyLabel->setModifier($this->getUser());
                            $propertyLabel->setCreationTime(new \DateTime('now'));
                            $propertyLabel->setModificationTime(new \DateTime('now'));

                            $property->addLabel($propertyLabel);
                            $em->persist($propertyLabel);

                            if (is_null($defaultStandardLabelEn) || $propertyLabel->getLanguageIsoCode() == "en") {
                                $defaultStandardLabelEn = (string) $nodeXmlLabel->standardLabel;
                                if (!is_null($propertyLabel->getInverseLabel())) {
                                    $defaultStandardLabelEn .= " (" . $propertyLabel->getInverseLabel() . ")";
                                }
                            }
                            if (is_null($defaultStandardLabelFr) || $propertyLabel->getLanguageIsoCode() == "fr") {
                                $defaultStandardLabelFr = (string) $nodeXmlLabel->standardLabel;
                                if (!is_null($propertyLabel->getInverseLabel())) {
                                    $defaultStandardLabelFr .= " (" . $propertyLabel->getInverseLabel() . ")";
                                }
                            }
                            if (is_null($defaultStandardLabel)) {
                                $defaultStandardLabel = (string) $nodeXmlLabel->standardLabel;
                                if (!is_null($propertyLabel->getInverseLabel())) {
                                    $defaultStandardLabel .= " (" . $propertyLabel->getInverseLabel() . ")";
                                }
                            }
                        }
                        if (!is_null($defaultStandardLabelEn)) {
                            $newPropertyVersion->setStandardLabel($defaultStandardLabelEn);
                        } elseif (!is_null($defaultStandardLabelFr)) {
                            $newPropertyVersion->setStandardLabel($defaultStandardLabelEn);
                        } else {
                            $newPropertyVersion->setStandardLabel($defaultStandardLabel);
                        }

                        $property->addPropertyVersion($newPropertyVersion);
                        $em->persist($newPropertyVersion);

                        // Scope note: un par langue
                        $langCollection = new ArrayCollection();
                        if (!is_null($nodeXmlProperty->textProperties->scopeNote)) {
                            foreach ($nodeXmlProperty->textProperties->scopeNote as $keySn => $nodeXmlScopeNote) {
                                if (!$langCollection->contains((string) $nodeXmlScopeNote->attributes()->lang)) {
                                    $langCollection->add((string) $nodeXmlScopeNote->attributes()->lang);
                                } else {
                                    $this->addFlash('error', "2 scope notes at least have the same language " . (string) $nodeXmlScopeNote->attributes()->lang . " for class " . $newClassVersion->getClass()->getIdentifierInNamespace());
                                    return $redirectRoute;
                                }
                                $scopeNote = new TextProperty();
                                $scopeNote->setProperty($property);
                                $scopeNote->setNamespaceForVersion($newNamespaceVersion);
                                $scopeNote->setTextProperty((string) $nodeXmlScopeNote);
                                $scopeNote->setLanguageIsoCode((string) $nodeXmlScopeNote->attributes()->lang);
                                $scopeNote->setSystemType($systemTypeScopeNote);
                                $scopeNote->setCreator($this->getUser());
                                $scopeNote->setModifier($this->getUser());
                                $scopeNote->setCreationTime(new \DateTime('now'));
                                $scopeNote->setModificationTime(new \DateTime('now'));

                                $property->addTextProperty($scopeNote);
                                $em->persist($scopeNote);
                            }
                        }
                        // Examples
                        if (!is_null($nodeXmlProperty->textProperties->example)) {
                            foreach ($nodeXmlProperty->textProperties->example as $keyEx => $nodeXmlExample) {
                                $example = new TextProperty();
                                $example->setProperty($property);
                                $example->setNamespaceForVersion($newNamespaceVersion);
                                $example->setTextProperty("<p>" . (string) $nodeXmlExample . "</p>");
                                $example->setLanguageIsoCode((string) $nodeXmlExample->attributes()->lang);
                                $example->setSystemType($systemTypeExample);
                                $example->setCreator($this->getUser());
                                $example->setModifier($this->getUser());
                                $example->setCreationTime(new \DateTime('now'));
                                $example->setModificationTime(new \DateTime('now'));
                                $property->addTextProperty($example);
                                $em->persist($example);
                            }
                        }

                        // Context note
                        if (!is_null($nodeXmlProperty->textProperties->contextNote)) {
                            foreach ($nodeXmlProperty->textProperties->contextNote as $keyEx => $nodeXmlContextNote) {
                                $contextNote = new TextProperty();
                                $contextNote->setProperty($property);
                                $contextNote->setNamespaceForVersion($newNamespaceVersion);
                                $contextNote->setEntityNamespaceForVersion($newNamespaceVersion);
                                $contextNote->setTextProperty("<p>" . (string) $nodeXmlContextNote . "</p>");
                                $contextNote->setLanguageIsoCode((string) $nodeXmlContextNote->attributes()->lang);
                                $contextNote->setSystemType($systemTypeContextNote);
                                $contextNote->setCreator($this->getUser());
                                $contextNote->setModifier($this->getUser());
                                $contextNote->setCreationTime(new \DateTime('now'));
                                $contextNote->setModificationTime(new \DateTime('now'));

                                $property->addTextProperty($contextNote);
                                $em->persist($contextNote);
                            }
                        }

                        // Bibliographical note
                        if (!is_null($nodeXmlProperty->textProperties->bibliographicalNote)) {
                            foreach ($nodeXmlProperty->textProperties->bibliographicalNote as $keyEx => $nodeXmlBibliographicalNote) {
                                $bibliographicalNote = new TextProperty();
                                $bibliographicalNote->setProperty($property);
                                $bibliographicalNote->setNamespaceForVersion($newNamespaceVersion);
                                $bibliographicalNote->setEntityNamespaceForVersion($newNamespaceVersion);
                                $bibliographicalNote->setTextProperty("<p>" . (string) $nodeXmlBibliographicalNote . "</p>");
                                $bibliographicalNote->setLanguageIsoCode((string) $nodeXmlBibliographicalNote->attributes()->lang);
                                $bibliographicalNote->setSystemType($systemTypeBibliographicalNote);
                                $bibliographicalNote->setCreator($this->getUser());
                                $bibliographicalNote->setModifier($this->getUser());
                                $bibliographicalNote->setCreationTime(new \DateTime('now'));
                                $bibliographicalNote->setModificationTime(new \DateTime('now'));

                                $property->addTextProperty($bibliographicalNote);
                                $em->persist($bibliographicalNote);
                            }
                        }
                    }

                    // Les entités ont été créées. Maintenant on passe aux relations hierarchiques/autres
                    foreach ($nodeXmlClasses->children() as $key => $nodeXmlClass) {

                        //SubClassOf
                        if (!empty($nodeXmlClass->subClassOf)) {
                            foreach ($nodeXmlClass->subClassOf as $keySub => $nodeXmlSubClassOf) {
                                $classAssociation = new ClassAssociation();
                                // Quelle version Parent ?
                                $xmlParentClassNamespace = $nodeXmlSubClassOf->attributes()->referenceNamespace;
                                //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                                if (!is_null($xmlParentClassNamespace)) {
                                    $parentClassNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                        ->findOneBy(array("id" => (integer) $xmlParentClassNamespace));
                                    if (!$idsReferences->contains((integer) $xmlParentClassNamespace)) {
                                        $this->addFlash('error', "A reference namespace for subclassOf has not been declared with the referenceNamespace tag. Please add it.<br>Problematic class: " . (string) $nodeXmlClass->identifierInNamespace . " - reference ID: " . (integer) $xmlParentClassNamespace);
                                        return $redirectRoute;
                                    }
                                } else {
                                    $parentClassNamespace = $newNamespaceVersion;
                                }
                                // Trouver la classe parente
                                $parentClass = null;
                                foreach ($parentClassNamespace->getClasses() as $tempClass) {
                                    if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlSubClassOf) {
                                        $parentClass = $tempClass;
                                        break;
                                    }
                                }
                                if (is_null($parentClass)) {
                                    $this->addFlash('error', (string) $nodeXmlClass->identifierInNamespace . " Parent class " . (string) $nodeXmlSubClassOf . " (" . $parentClassNamespace->getId() . ") was not found.");
                                    return $redirectRoute;
                                }

                                // Trouver la classe enfante
                                $childClass = null;
                                foreach ($newNamespaceVersion->getClasses() as $tempClass) {
                                    if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlClass->identifierInNamespace) {
                                        $childClass = $tempClass;
                                        break;
                                    }
                                }
                                if (is_null($childClass)) {
                                    $this->addFlash('error', (string) $nodeXmlClass->identifierInNamespace . " Child class " . (string) $nodeXmlClass->identifierInNamespace . " was not found.");
                                    return $redirectRoute;
                                }

                                //TODO Justification ClassAssociation?
                                $classAssociation->setParentClass($parentClass);
                                $classAssociation->setParentClassNamespace($parentClassNamespace);
                                $classAssociation->setChildClass($childClass);
                                $classAssociation->setChildClassNamespace($newNamespaceVersion);

                                $classAssociation->setNamespaceForVersion($newNamespaceVersion);

                                $classAssociation->setCreator($this->getUser());
                                $classAssociation->setModifier($this->getUser());
                                $classAssociation->setCreationTime(new \DateTime('now'));
                                $classAssociation->setModificationTime(new \DateTime('now'));

                                $newNamespaceVersion->addClassAssociation($classAssociation);
                                $em->persist($classAssociation);
                            }
                        }

                        //parentClassOf
                        if (!empty($nodeXmlClass->parentClassOf)) {
                            foreach ($nodeXmlClass->parentClassOf as $keySub => $nodeXmlParentClassOf) {
                                $classAssociation = new ClassAssociation();
                                // Quelle version Child ?
                                $xmlChildClassNamespace = $nodeXmlParentClassOf->attributes()->referenceNamespace;
                                //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                                if (!is_null($xmlChildClassNamespace)) {
                                    $childClassNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                        ->findOneBy(array("id" => (integer) $xmlChildClassNamespace));
                                    if (!$idsReferences->contains((integer) $xmlChildClassNamespace)) {
                                        $this->addFlash('error', "A reference namespace for parentClassOf has not been declared with the referenceNamespace tag. Please add it.<br>Problematic class: " . (string) $nodeXmlClass->identifierInNamespace . " - reference ID: " . (integer) $xmlChildClassNamespace);
                                        return $redirectRoute;
                                    }
                                } else {
                                    $childClassNamespace = $newNamespaceVersion;
                                }
                                // Trouver la classe child
                                $childClass = null;
                                foreach ($childClassNamespace->getClasses() as $tempClass) {
                                    if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlParentClassOf) {
                                        $childClass = $tempClass;
                                        break;
                                    }
                                }
                                if (is_null($childClass)) {
                                    $this->addFlash('error', (string) $nodeXmlClass->identifierInNamespace . " Child class " . (string) $nodeXmlParentClassOf . " (" . $childClassNamespace->getId() . ") was not found.");
                                    return $redirectRoute;
                                }

                                // Trouver la classe parente
                                $parentClass = null;
                                foreach ($newNamespaceVersion->getClasses() as $tempClass) {
                                    if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlClass->identifierInNamespace) {
                                        $parentClass = $tempClass;
                                        break;
                                    }
                                }
                                if (is_null($parentClass)) {
                                    $this->addFlash('error', (string) $nodeXmlClass->identifierInNamespace . " Parent class " . (string) $nodeXmlClass->identifierInNamespace . " was not found.");
                                    return $redirectRoute;
                                }

                                //TODO Justification ClassAssociation?
                                $classAssociation->setParentClass($parentClass);
                                $classAssociation->setParentClassNamespace($newNamespaceVersion);
                                $classAssociation->setChildClass($childClass);
                                $classAssociation->setChildClassNamespace($childClassNamespace);

                                $classAssociation->setNamespaceForVersion($newNamespaceVersion);

                                $classAssociation->setCreator($this->getUser());
                                $classAssociation->setModifier($this->getUser());
                                $classAssociation->setCreationTime(new \DateTime('now'));
                                $classAssociation->setModificationTime(new \DateTime('now'));

                                $newNamespaceVersion->addClassAssociation($classAssociation);
                                $em->persist($classAssociation);
                            }
                        }

                        //equivalentClass or disjointWith
                        foreach ($nodeXmlClass->children() as $key => $value) {
                            if ($key == "equivalentClass" || $key == "disjointWith") {
                                $entityAssociation = new EntityAssociation();
                                // Quelle version Target ?
                                if ($key == "equivalentClass") {
                                    $nodeXmlEntityAssociation = $nodeXmlClass->equivalentClass;
                                }
                                if ($key == "disjointWith") {
                                    $nodeXmlEntityAssociation = $nodeXmlClass->disjointWith;
                                }
                                $xmlTargetClassNamespace = $nodeXmlEntityAssociation->attributes()->referenceNamespace;
                                //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                                if (!is_null($xmlTargetClassNamespace)) {
                                    $targetClassNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                        ->findOneBy(array("id" => (integer) $xmlTargetClassNamespace));
                                    if (!$idsReferences->contains((integer) $xmlTargetClassNamespace)) {
                                        $this->addFlash('error', "A reference namespace for targetClass, equivalentClass or disjointWith has not been declared with the referenceNamespace tag. Please add it.<br>Problematic class: " . (string) $nodeXmlClass->identifierInNamespace . " - reference ID: " . (integer) $xmlTargetClassNamespace);
                                        return $redirectRoute;
                                    }
                                } else {
                                    $targetClassNamespace = $newNamespaceVersion;
                                }
                                // Trouver la classe cible
                                $targetClass = null;
                                foreach ($targetClassNamespace->getClasses() as $tempClass) {
                                    if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlEntityAssociation) {
                                        $targetClass = $tempClass;
                                        break;
                                    }
                                }
                                if (is_null($targetClass)) {
                                    $this->addFlash('error', (string) $nodeXmlClass->identifierInNamespace . " Target class " . (string) $nodeXmlEntityAssociation . " was not found.");
                                    return $redirectRoute;
                                }

                                // Trouver la classe source
                                $sourceClass = null;
                                foreach ($newNamespaceVersion->getClasses() as $tempClass) {
                                    if ($tempClass->getIdentifierInNamespace() == (string) $nodeXmlClass->identifierInNamespace) {
                                        $sourceClass = $tempClass;
                                        break;
                                    }
                                }
                                if (is_null($sourceClass)) {
                                    $this->addFlash('error', (string) $nodeXmlClass->identifierInNamespace . " Source class " . (string) $nodeXmlClass->identifierInNamespace . " was not found.");
                                    return $redirectRoute;
                                }
                                $entityAssociation->setSourceClass($sourceClass);
                                $entityAssociation->setSourceNamespaceForVersion($newNamespaceVersion);
                                $entityAssociation->setTargetClass($targetClass);
                                $entityAssociation->setTargetNamespaceForVersion($targetClassNamespace);

                                $entityAssociation->setNamespaceForVersion($newNamespaceVersion);

                                $entityAssociation->setCreator($this->getUser());
                                $entityAssociation->setModifier($this->getUser());
                                $entityAssociation->setCreationTime(new \DateTime('now'));
                                $entityAssociation->setModificationTime(new \DateTime('now'));

                                $entityAssociation->setDirected(false);

                                if ($key == "equivalentClass") {
                                    $systemTypeEquivalentClass = $em->getRepository('AppBundle:SystemType')->find(18); //owl:equivalentClass
                                    $entityAssociation->setSystemType($systemTypeEquivalentClass);
                                }
                                if ($key == "disjointWith") {
                                    $systemTypeDisjointWith = $em->getRepository('AppBundle:SystemType')->find(19); //owl:disjointWith
                                    $entityAssociation->setSystemType($systemTypeDisjointWith);
                                }

                                $newNamespaceVersion->addEntityAssociation($entityAssociation);
                                $em->persist($entityAssociation);
                            }
                        }
                    }
                    foreach ($nodeXmlProperties->children() as $key => $nodeXmlProperty) {
                        //subPropertyOf
                        if (!empty($nodeXmlProperty->subPropertyOf)) {
                            foreach ($nodeXmlProperty->subPropertyOf as $keySub => $nodeXmlSubPropertyOf) {
                                $propertyAssociation = new PropertyAssociation();
                                // Quelle version Parent ?
                                $xmlParentPropertyNamespace = $nodeXmlSubPropertyOf->attributes()->referenceNamespace;
                                //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                                if (!is_null($xmlParentPropertyNamespace)) {
                                    $parentPropertyNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                        ->findOneBy(array("id" => (integer) $xmlParentPropertyNamespace));
                                    if (!$idsReferences->contains((integer) $xmlParentPropertyNamespace)) {
                                        $this->addFlash('error', "A reference namespace for subPropertyOf has not been declared with the referenceNamespace tag. Please add it.<br>Problematic property: " . (string) $nodeXmlProperty->identifierInNamespace . " - reference ID: " . (integer) $xmlParentPropertyNamespace);
                                        return $redirectRoute;
                                    }
                                } else {
                                    $parentPropertyNamespace = $newNamespaceVersion;
                                }
                                // Trouver la propriété parente
                                $parentProperty = null;
                                foreach ($parentPropertyNamespace->getProperties() as $tempProperty) {
                                    if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlSubPropertyOf) {
                                        $parentProperty = $tempProperty;
                                        break;
                                    }
                                }
                                if (is_null($parentProperty)) {
                                    $this->addFlash('error', (string) $nodeXmlProperty->identifierInNamespace . " Parent property " . (string) $nodeXmlSubPropertyOf . " was not found");
                                    return $redirectRoute;
                                }

                                // Trouver la propriété enfante
                                $childProperty = null;
                                foreach ($newNamespaceVersion->getProperties() as $tempProperty) {
                                    if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlProperty->identifierInNamespace) {
                                        $childProperty = $tempProperty;
                                        break;
                                    }
                                }
                                if (is_null($childProperty)) {
                                    $this->addFlash('error', (string) $nodeXmlProperty->identifierInNamespace . " Child property " . (string) $nodeXmlProperty->identifierInNamespace . " was not found");
                                    return $redirectRoute;
                                }
                                //TODO Justification PropertyAssociation?
                                $propertyAssociation->setParentProperty($parentProperty);
                                $propertyAssociation->setParentPropertyNamespace($parentPropertyNamespace);
                                $propertyAssociation->setChildProperty($childProperty);
                                $propertyAssociation->setChildPropertyNamespace($newNamespaceVersion);

                                $propertyAssociation->setNamespaceForVersion($newNamespaceVersion);

                                $propertyAssociation->setCreator($this->getUser());
                                $propertyAssociation->setModifier($this->getUser());
                                $propertyAssociation->setCreationTime(new \DateTime('now'));
                                $propertyAssociation->setModificationTime(new \DateTime('now'));

                                $newNamespaceVersion->addPropertyAssociation($propertyAssociation);
                                $em->persist($propertyAssociation);

                            }
                        }

                        //parentPropertyOf
                        if (!empty($nodeXmlProperty->parentPropertyOf)) {
                            foreach ($nodeXmlProperty->parentPropertyOf as $keySub => $nodeXmlParentPropertyOf) {
                                $propertyAssociation = new PropertyAssociation();
                                // Quelle version Child ?
                                $xmlChildPropertyNamespace = $nodeXmlParentPropertyOf->attributes()->referenceNamespace;
                                //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                                if (!is_null($xmlChildPropertyNamespace)) {
                                    $childPropertyNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                        ->findOneBy(array("id" => (integer) $xmlChildPropertyNamespace));
                                    if (!$idsReferences->contains((integer) $xmlChildPropertyNamespace)) {
                                        $this->addFlash('error', "A reference namespace for parentPropertyOf has not been declared with the referenceNamespace tag. Please add it.<br>Problematic property: " . (string) $nodeXmlProperty->identifierInNamespace . " - reference ID: " . (integer) $xmlChildPropertyNamespace);
                                        return $redirectRoute;
                                    }
                                } else {
                                    $childPropertyNamespace = $newNamespaceVersion;
                                }
                                // Trouver la propriété enfante
                                $childProperty = null;
                                foreach ($childPropertyNamespace->getProperties() as $tempProperty) {
                                    if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlParentPropertyOf) {
                                        $childProperty = $tempProperty;
                                        break;
                                    }
                                }
                                if (is_null($childProperty)) {
                                    $this->addFlash('error', (string) $nodeXmlProperty->identifierInNamespace . " Child property " . (string) $nodeXmlParentPropertyOf . " n'a pas été trouvé");
                                    return $redirectRoute;
                                }

                                // Trouver la propriété parente
                                $parentProperty = null;
                                foreach ($newNamespaceVersion->getProperties() as $tempProperty) {
                                    if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlProperty->identifierInNamespace) {
                                        $parentProperty = $tempProperty;
                                        break;
                                    }
                                }
                                if (is_null($parentProperty)) {
                                    $this->addFlash('error', (string) $nodeXmlProperty->identifierInNamespace . " Parent property " . (string) $nodeXmlProperty->identifierInNamespace . " n'a pas été trouvé");
                                    return $redirectRoute;
                                }
                                //TODO Justification PropertyAssociation?
                                $propertyAssociation->setParentProperty($parentProperty);
                                $propertyAssociation->setParentPropertyNamespace($newNamespaceVersion);
                                $propertyAssociation->setChildProperty($childProperty);
                                $propertyAssociation->setChildPropertyNamespace($childPropertyNamespace);

                                $propertyAssociation->setNamespaceForVersion($newNamespaceVersion);

                                $propertyAssociation->setCreator($this->getUser());
                                $propertyAssociation->setModifier($this->getUser());
                                $propertyAssociation->setCreationTime(new \DateTime('now'));
                                $propertyAssociation->setModificationTime(new \DateTime('now'));

                                $newNamespaceVersion->addPropertyAssociation($propertyAssociation);
                                $em->persist($propertyAssociation);

                            }
                        }

                        //equivalentProperty or inverseOf
                        foreach ($nodeXmlProperty->children() as $key => $value) {
                            if ($key == "equivalentProperty" || $key == "inverseOf") {
                                $entityAssociation = new EntityAssociation();
                                // Quelle version Target ?
                                if ($key == "equivalentProperty") {
                                    $nodeXmlEntityAssociation = $nodeXmlProperty->equivalentProperty;
                                }
                                if ($key == "inverseOf") {
                                    $nodeXmlEntityAssociation = $nodeXmlProperty->inverseOf;
                                }
                                $xmlTargetPropertyNamespace = $nodeXmlEntityAssociation->attributes()->referenceNamespace;
                                //Si attribut referenceNamespace existe, utiliser cet id, sinon ce nouveau namespace
                                if (!is_null($xmlTargetPropertyNamespace)) {
                                    $targetPropertyNamespace = $em->getRepository("AppBundle:OntoNamespace")
                                        ->findOneBy(array("id" => (integer) $xmlTargetPropertyNamespace));
                                    if (!$idsReferences->contains((integer) $xmlTargetPropertyNamespace)) {
                                        $this->addFlash('error', "A reference namespace for targetProperty, equivalentProperty or inverseOf has not been declared with the referenceNamespace tag. Please add it.<br>Problematic property: " . (string) $nodeXmlProperty->identifierInNamespace . " - reference ID: " . (integer) $xmlTargetPropertyNamespace);
                                        return $redirectRoute;
                                    }
                                } else {
                                    $targetPropertyNamespace = $newNamespaceVersion;
                                }
                                // Trouver la propriété cible
                                $targetProperty = null;
                                foreach ($targetPropertyNamespace->getProperties() as $tempProperty) {
                                    if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlEntityAssociation) {
                                        $targetProperty = $tempProperty;
                                        break;
                                    }
                                }
                                if (is_null($targetProperty)) {
                                    $this->addFlash('error', (string) $nodeXmlProperty->identifierInNamespace . " Target property " . (string) $nodeXmlEntityAssociation . " was not found");
                                    return $redirectRoute;
                                }

                                // Trouver la propriété enfante
                                $sourceProperty = null;
                                foreach ($newNamespaceVersion->getProperties() as $tempProperty) {
                                    if ($tempProperty->getIdentifierInNamespace() == (string) $nodeXmlProperty->identifierInNamespace) {
                                        $sourceProperty = $tempProperty;
                                        break;
                                    }
                                }
                                if (is_null($sourceProperty)) {
                                    $this->addFlash('error', (string) $nodeXmlProperty->identifierInNamespace . " Source property " . (string) $nodeXmlProperty->identifierInNamespace . " n'a pas été trouvé");
                                    return $redirectRoute;
                                }
                                $entityAssociation->setSourceProperty($sourceProperty);
                                $entityAssociation->setSourceNamespaceForVersion($newNamespaceVersion);
                                $entityAssociation->setTargetProperty($targetProperty);
                                $entityAssociation->setTargetNamespaceForVersion($targetPropertyNamespace);

                                $entityAssociation->setNamespaceForVersion($newNamespaceVersion);

                                $entityAssociation->setCreator($this->getUser());
                                $entityAssociation->setModifier($this->getUser());
                                $entityAssociation->setCreationTime(new \DateTime('now'));
                                $entityAssociation->setModificationTime(new \DateTime('now'));

                                $entityAssociation->setDirected(false);

                                if ($key == "equivalentProperty") {
                                    $systemTypeEquivalentProperty = $em->getRepository('AppBundle:SystemType')->find(18); //owl:equivalentProperty
                                    $entityAssociation->setSystemType($systemTypeEquivalentProperty);
                                }
                                if ($key == "inverseOf") {
                                    $systemTypeInverseOf = $em->getRepository('AppBundle:SystemType')->find(20); //owl:inverseOf
                                    $entityAssociation->setSystemType($systemTypeInverseOf);
                                }

                                $newNamespaceVersion->addEntityAssociation($entityAssociation);
                                $em->persist($entityAssociation);
                            }
                        }
                    }

                    $newNamespaceVersion->setCreator($this->getUser());
                    $newNamespaceVersion->setModifier($this->getUser());
                    $newNamespaceVersion->setCreationTime(new \DateTime('now'));
                    $newNamespaceVersion->setModificationTime(new \DateTime('now'));
                    $newNamespaceVersion->setIsTopLevelNamespace(false);
                    $newNamespaceVersion->setProjectForTopLevelNamespace($project);
                    $newNamespaceVersion->setIsOngoing(false);
                    $newNamespaceVersion->setIsExternalNamespace(true);
                    $newNamespaceVersion->setReferencedVersion($namespaceRoot);

                    $em->persist($newNamespaceVersion);
                    $em->flush();
                    $this->addFlash('success', 'Namespace imported!');

                } else {
                    $this->addFlash('error', 'The uploaded XML file is not well-formed. Please check the file structure and try again.');
                    return $redirectRoute;
                }
            } else {
                $this->addFlash('error', "The uploaded file must be a valid XML file. Please check the file format and ensure it has the correct MIME type.");
                return $redirectRoute;
            }

            //TODO: Normalement on n'arrive jamais ici ?
            $this->addFlash('error', 'An unexpected error occurred during the import process. Please try again.');
            return $redirectRoute;
        }

        $rootNamespaces = $em->getRepository('AppBundle:OntoNamespace')
            ->findBy(array('isTopLevelNamespace' => true));
        $rootNamespaces = array_filter($rootNamespaces, function ($v) {
            return $v->getId() != 5;
        });

        return $this->render('project/edit.html.twig', array(
            'project' => $project,
            'formImport' => $formImport->createView(),
            'namespacesPublicProject' => $namespacesPublicProject,
            'associatedNamespacesForAPIProject' => $associatedNamespacesForAPIProject,
            'rootNamespaces' => $rootNamespaces,
            'users' => $users
        ));
    }

    /**
     * @Route("/selectable-members/project/{project}/json", name="selectable_members_project_json", requirements={"project"="^([0-9]+)|(projectID){1}$"})
     * @Method("GET")
     * @param Project $project
     * @return JsonResponse a Json formatted list representation of Users selectable by Project
     */
    public function getSelectableMembersByProject(Project $project)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $users = $em->getRepository('AppBundle:User')
                ->findAllNotInProject($project);
            $data['data'] = $users;
            $data = json_encode($data);
        }
        catch (NotFoundHttpException $e) {
            return new JsonResponse(null,404, 'content-type:application/problem+json');
        }

        if(empty($users)) {
            return new JsonResponse(null,204, array());
        }

        return new JsonResponse($data,200, array(), true);
    }

    /**
     * @Route("/associated-members/project/{project}/json", name="associated_members_project_json", requirements={"project"="^([0-9]+)|(projectID){1}$"})
     * @Method("GET")
     * @param Project $project
     * @return JsonResponse a Json formatted list representation of Users selected by Project
     */
    public function getAssociatedMembersByProject(Project $project)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $classes = $em->getRepository('AppBundle:User')
                ->findUsersInProject($project);
            $data['data'] = $classes;
            $data = json_encode($data);
        }
        catch (NotFoundHttpException $e) {
            return new JsonResponse(null,404, 'content-type:application/problem+json');
        }

        if(empty($classes)) {
            return new JsonResponse(null,204, array());
        }

        return new JsonResponse($data,200, array(), true);
    }

    /**
     * @Route("/project/{project}/user/{user}/add", name="project_user_association", requirements={"project"="^([0-9]+)|(projectID){1}$", "user"="^([0-9]+)|(id){1}$"})
     * @Method({ "POST"})
     * @param User  $user    The user to be associated with a project
     * @param Project  $project    The project to be associated with a user
     * @throws \Exception in case of unsuccessful association
     * @return JsonResponse $response
     */
    public function newProjectUserAssociationAction(Project $project, User $user, Request $request)
    {
        $this->denyAccessUnlessGranted('edit_manager', $project);

        $em = $this->getDoctrine()->getManager();
        $userProjectAssociation = $em->getRepository('AppBundle:UserProjectAssociation')
            ->findOneBy(array('project' => $project->getId(), 'user' => $user->getId()));

        if (!is_null($userProjectAssociation)) {
            $status = 'Error';
            $message = 'This user is already member of this project.';
        }
        else {
            $em = $this->getDoctrine()->getManager();

            $userProjectAssociation = new UserProjectAssociation();
            $userProjectAssociation->setProject($project);
            $userProjectAssociation->setUser($user);
            $userProjectAssociation->setPermission(3); //status 3 = member
            $userProjectAssociation->setCreator($this->getUser());
            $userProjectAssociation->setModifier($this->getUser());
            $userProjectAssociation->setCreationTime(new \DateTime('now'));
            $userProjectAssociation->setModificationTime(new \DateTime('now'));
            $em->persist($userProjectAssociation);

            $em->flush();
            $status = 'Success';
            $message = 'Member successfully associated.';
        }


        $response = array(
            'status' => $status,
            'message' => $message
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/project/{project}/namespace/{namespace}/add", name="project_namespace_association", requirements={"project"="^([0-9]+)|(projectID){1}$", "namespace"="^([0-9]+)|(selectedValue){1}$"})
     * @Method({ "POST"})
     * @param OntoNamespace $namespace    The namespace to be associated with a project API
     * @param Project  $project    The project API to be associated with a namespace
     * @throws \Exception in case of unsuccessful association
     * @return JsonResponse $response
     */
    public function newProjectNamespaceAssociationAction(Project $project, OntoNamespace $namespace, Request $request)
    {
        $this->denyAccessUnlessGranted('edit_manager', $project);

        $em = $this->getDoctrine()->getManager();

        $systemTypeAPISelected = $em->getRepository('AppBundle:SystemType')->find(38); //systemType 38 = Associated namespace for API Project

        $projectAssociation = $em->getRepository('AppBundle:ProjectAssociation')
            ->findOneBy(array('project' => $project->getId(), 'namespace' => $namespace->getId(), 'systemType' => 38));

        if (!is_null($projectAssociation)) {
            $status = 'Error';
            $message = 'This user is already member of this project.';
        }
        else {
            $em = $this->getDoctrine()->getManager();

            $projectAssociation = new ProjectAssociation();
            $projectAssociation->setProject($project);
            $projectAssociation->setNamespace($namespace);
            $projectAssociation->setSystemType($systemTypeAPISelected);
            $projectAssociation->setCreator($this->getUser());
            $projectAssociation->setModifier($this->getUser());
            $projectAssociation->setCreationTime(new \DateTime('now'));
            $projectAssociation->setModificationTime(new \DateTime('now'));
            $em->persist($projectAssociation);

            $em->flush();
            $status = 'Success';
            $message = 'Namespace successfully associated in API.';
        }


        $response = array(
            'status' => $status,
            'message' => $message,
            'idProjectAssociation' => $projectAssociation->getId()
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/user-project-association/{id}/permission/{permission}/edit", name="project_member_permission_edit", requirements={"id"="^([0-9]+)|(associationId){1}$", "permission"="^([1-4])|(permissionToken){1}$"})
     * @Method({ "POST"})
     * @param UserProjectAssociation  $userProjectAssociation   The user to project association to be edited
     * @param int  $permission    The permission to
     * @throws \Exception in case of unsuccessful association
     * @return JsonResponse $response
     */
    public function editProjectUserAssociationPermissionAction(UserProjectAssociation $userProjectAssociation, $permission, Request $request)
    {
        $this->denyAccessUnlessGranted('full_edit', $userProjectAssociation->getProject());

        if($userProjectAssociation->getUser() == $this->getUser()) {
            //l'utilisateur connecté ne peut pas changer ses propres permissions
            $status = 'Error';
            $message = 'The current user cannot change his own permission.';
        }
        else {
            try{
                $em = $this->getDoctrine()->getManager();

                $userProjectAssociation->setPermission($permission);
                $userProjectAssociation->setModifier($this->getUser());
                $em->persist($userProjectAssociation);
                $em->flush();
                $status = 'Success';
                $message = 'Permission successfully edited.';
            }
            catch (\Exception $e) {
                return new JsonResponse(null, 400, 'content-type:application/problem+json');
            }
        }

        $response = array(
            'status' => $status,
            'message' => $message
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/user-project-association/{id}/delete", name="project_member_disassociation", requirements={"id"="^([0-9]+)|(associationId){1}$"})
     * @Method({ "POST"})
     * @param UserProjectAssociation  $userProjectAssociation   The user to project association to be deleted
     * @return JsonResponse a Json 204 HTTP response
     */
    public function deleteProjectUserAssociationAction(UserProjectAssociation $userProjectAssociation, Request $request)
    {
        $this->denyAccessUnlessGranted('edit_manager', $userProjectAssociation->getProject());
        try {
            $em = $this->getDoctrine()->getManager();
            $project = $userProjectAssociation->getProject();
            $user = $userProjectAssociation->getUser();

            // Si l'utilisateur l'a en projet actif, modifier pour éviter qu'il se retrouve bloqué
            if($user->getCurrentActiveProject() == $project){
                $publicProject = $em->getRepository('AppBundle:Project')->find(21);
                $user->setCurrentActiveProject($publicProject);
                $em->persist($user);
            }

            $entityUserProjectsAssociations = $em->getRepository('AppBundle:EntityUserProjectAssociation')
                ->findBy(array('userProjectAssociation' => $userProjectAssociation->getId()));
            foreach ($entityUserProjectsAssociations as $eupa){
                $em->remove($eupa);
            }
            $em->remove($userProjectAssociation);
            $em->flush();
        }
        catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400, array('content-type:application/problem+json'));
        }
        return new JsonResponse(null, 204);

    }

    /**
     * @Route("/selectable-profiles/project/{project}/json", name="selectable_profiles_project_json", requirements={"project"="^([0-9]+)|(projectID){1}$"})
     * @Method("GET")
     * @param Project $project
     * @return JsonResponse a Json formatted list representation of Profiles selectable by Project
     */
    public function getSelectableProfilesByProject(Project $project)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $profiles = $em->getRepository('AppBundle:Profile')
                ->findProfilesForAssociationWithProjectByProjectId($project);
            $data['data'] = $profiles;
            $data = json_encode($data);
        }
        catch (NotFoundHttpException $e) {
            return new JsonResponse(null,404, 'content-type:application/problem+json');
        }

        if(empty($profiles)) {
            return new JsonResponse(null,204, array());
        }

        return new JsonResponse($data,200, array(), true);
    }

    /**
     * @Route("/associated-profiles/project/{project}/json", name="associated_profiles_project_json", requirements={"project"="^([0-9]+)|(projectID){1}$"})
     * @Method("GET")
     * @param Project $project
     * @return JsonResponse a Json formatted list representation of Profiles associated with Project
     */
    public function getAssociatedProfilesByProject(Project $project)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $profiles = $em->getRepository('AppBundle:Profile')
                ->findProfilesByProjectId($project);
            $data['data'] = $profiles;
            $data = json_encode($data);
        }
        catch (NotFoundHttpException $e) {
            return new JsonResponse(null,404, 'content-type:application/problem+json');
        }

        if(empty($profiles)) {
            return new JsonResponse('{"data":[]}',200, array(), true);
        }

        return new JsonResponse($data,200, array(), true);
    }

    /**
     * @Route("/project/{project}/profile/{profile}/add", name="project_profile_association", requirements={"project"="^([0-9]+)|(projectID){1}$", "profile"="^([0-9]+)|(profileID){1}$"})
     * @Method({ "POST"})
     * @param Profile  $profile The profile to be associated with a project
     * @param Project  $project   The project to be associated with a profile
     * @throws \Exception in case of unsuccessful association
     * @return JsonResponse a Json formatted namespaces list
     */
    public function newProjectProfileAssociationAction(Profile $profile, Project $project, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        $em = $this->getDoctrine()->getManager();
        $projectAssociation = $em->getRepository('AppBundle:ProjectAssociation')
            ->findOneBy(array('project' => $project->getId(), 'profile' => $profile->getId()));

        if (!is_null($projectAssociation)) {
            if($projectAssociation->getSystemType()->getId() == 11) {
                $status = 'Error';
                $message = 'This profile is already used by this project';
            }
            else {
                $systemType = $em->getRepository('AppBundle:SystemType')->find(11); //systemType 11 = Used by project
                $projectAssociation->setSystemType($systemType);

                $em->persist($projectAssociation);
                $em->flush();

                $status = 'Success';
                $message = 'Profile successfully re-associated';
            }
        }
        else {
            $em = $this->getDoctrine()->getManager();

            $projectAssociation = new ProjectAssociation();
            $projectAssociation->setProject($project);
            $projectAssociation->setProfile($profile);
            $systemType = $em->getRepository('AppBundle:SystemType')->find(11); //systemType 11 = Used by project
            $projectAssociation->setSystemType($systemType);
            $projectAssociation->setCreator($this->getUser());
            $projectAssociation->setModifier($this->getUser());
            $projectAssociation->setCreationTime(new \DateTime('now'));
            $projectAssociation->setModificationTime(new \DateTime('now'));
            $em->persist($projectAssociation);

            $em->flush();
            $status = 'Success';
            $message = 'Profile successfully associated';
        }


        $response = array(
            'status' => $status,
            'message' => $message
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/project/{project}/profile/{profile}/delete", name="project_profile_disassociation", requirements={"project"="^([0-9]+)|(projectID){1}$", "profile"="^([0-9]+)|(profileID){1}$"})
     * @Method({ "POST"})
     * @param Profile  $profile    The profile to be disassociated from a project
     * @param Project  $project    The project to be disassociated from a profile
     * @return JsonResponse a Json 204 HTTP response
     */
    public function deleteProjectProfileAssociationAction(Profile $profile, Project $project, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $project);
        $em = $this->getDoctrine()->getManager();

        $projectAssociation = $em->getRepository('AppBundle:ProjectAssociation')
            ->findOneBy(array('project' => $project->getId(), 'profile' => $profile->getId()));

        $em->remove($projectAssociation);
        $em->flush();

        return new JsonResponse(null, 204);

    }
}