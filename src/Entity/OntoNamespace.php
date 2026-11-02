<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 20/06/2017
 * Time: 10:26.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OntoNamespace.
 */
#[ORM\Table(schema: 'che', name: 'namespace')]
#[ORM\Index(name: 'namespace_fk_top_level_namespace_idx', columns: ['fk_top_level_namespace'])]
#[ORM\Entity]
class OntoNamespace
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_namespace')]
    private int $id;

    #[Assert\Url(message: 'Please enter a valid URI')]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $namespaceURI = null;

    #[ORM\Column]
    private bool $isExternalNamespace;

    #[Assert\Url]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $originalNamespaceURI = null; 

    #[Assert\Length(
        min: 1, 
        max: 6, 
        minMessage: 'Your class prefix must be at least {{ limit }} characters long', 
        maxMessage: 'Your class prefix cannot be longer than {{ limit }} characters'
        )]
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $classPrefix = null;

    #[Assert\Length(
        min: 1, 
        max: 6, 
        minMessage: 'Your property prefix must be at least {{ limit }} characters long', 
        maxMessage: 'Your property prefix cannot be longer than {{ limit }} characters'
        )]
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $propertyPrefix = null;

    #[Assert\GreaterThanOrEqual(
        value: 0, 
        message: 'Your current class number should be greater than or equal to 0.'
        )]
    #[ORM\Column(nullable: true)]
    private ?int $currentClassNumber = null;

    #[Assert\GreaterThanOrEqual(
        value: 0, 
        message: 'Your current property number should be greater than or equal to 0.'
        )]
    #[ORM\Column(nullable: true)]
    private ?int $currentPropertyNumber = null;

    #[ORM\Column(nullable: true)]
    private ?int $importerInteger = null;

    #[ORM\ManyToOne(inversedBy: 'childVersions', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_is_version_of', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $referencedVersion = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_top_level_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $topLevelNamespace = null;

    #[ORM\Column]
    private bool $isTopLevelNamespace;

    #[ORM\Column]
    private bool $isOngoing;

    #[ORM\Column(options: ['default' => false])]
    private bool $isVisible = false;

    #[ORM\Column(nullable: true)]
    private ?bool $hasPublication = null; // TODO: A quoi ça sert exactement ?

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'managedNamespaces')]
    #[ORM\JoinColumn(name: 'fk_project_for_top_level_namespace', referencedColumnName: 'pk_project')]
    private Project $projectForTopLevelNamespace;

    #[ORM\Column(type: 'text')]
    private string $standardLabel;

    #[ORM\Column(nullable: true)]
    private ?DateTime $publishedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $deprecatedAt = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'namespace')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    private User $creator;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    private User $modifier;

    #[ORM\Column(options: ['default' => 'now'])]
    private DateTime $creationTime;

    #[ORM\Column]
    private DateTime $modificationTime;

    #[Assert\Length(
        min: 1, 
        max: 10, 
        minMessage: 'Your root namespace prefix must be at least {{ limit }} characters long', 
        maxMessage: 'Your root namespace prefix cannot be longer than {{ limit }} characters'
        )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9](-?[a-z0-9])*$/', 
        match: true, 
        message: 'Your prefix can only contain alphanumeric characters without accents and dashes'
        )]
    #[Assert\NotNull(groups: ['RequirePrefix'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $rootNamespacePrefix = null;

    /*
     * Pour les namespaces root (les namespaces versions n'auront pas besoin de champ = null)
     * 0: Entity identifier
     * 1: Entity identifier + label
     * 2: camelCase
     * 3: No parameter
     */
    #[ORM\Column(options: ['default' => 0])]
    private int $uriParameter;

    #[Assert\Valid]
    #[Assert\NotBlank]
    #[ORM\OneToMany(targetEntity: Label::class, mappedBy: 'namespace', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $labels;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'namespace', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $textProperties;

    #[ORM\OneToMany(targetEntity: ClassAssociation::class, mappedBy: 'namespaceForVersion')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $classAssociations;

    #[ORM\OneToMany(targetEntity: EntityAssociation::class, mappedBy: 'namespaceForVersion')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $entityAssociations;

    #[ORM\OneToMany(targetEntity: PropertyAssociation::class, mappedBy: 'namespaceForVersion')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $propertyAssociations;

    #[ORM\OneToMany(targetEntity: \EntityUserProjectAssociation::class, mappedBy: 'namespace')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $namespaceUserProjectAssociation;

    #[ORM\OneToMany(targetEntity: ProjectAssociation::class, mappedBy: 'namespace')]
    private Collection $projectAssociations;

    #[ORM\OneToMany(targetEntity: OntoNamespace::class, mappedBy: 'referencedVersion')]
    private Collection $childVersions;

    #[ORM\OneToMany(targetEntity: ReferencedNamespaceAssociation::class, mappedBy: 'namespace')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $referencedNamespaceAssociations;

    #[ORM\OneToMany(targetEntity: OntoClassVersion::class, mappedBy: 'namespaceForVersion')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $classVersions;

    #[ORM\OneToMany(targetEntity: PropertyVersion::class, mappedBy: 'namespaceForVersion')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $propertyVersions;

    #[ORM\ManyToMany(targetEntity: Container::class, mappedBy: 'namespaces')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $containers;

    public function getCurrentClassNumber()
    {
        return $this->currentClassNumber;
    }

    public function setCurrentClassNumber($currentClassNumber)
    {
        $this->currentClassNumber = $currentClassNumber;
    }

    public function getCurrentPropertyNumber()
    {
        return $this->currentPropertyNumber;
    }

    public function setCurrentPropertyNumber($currentPropertyNumber)
    {
        $this->currentPropertyNumber = $currentPropertyNumber;
    }

    public function getEntityAssociations()
    {
        return $this->entityAssociations;
    }

    public function setEntityAssociations($entityAssociations)
    {
        $this->entityAssociations = $entityAssociations;
    }

    public function getNamespaceUserProjectAssociation()
    {
        return $this->namespaceUserProjectAssociation;
    }

    public function setNamespaceUserProjectAssociation($namespaceUserProjectAssociation)
    {
        $this->namespaceUserProjectAssociation = $namespaceUserProjectAssociation;
    }

    /**
     * @return bool
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
    }

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->textProperties = new ArrayCollection();
        $this->classAssociations = new ArrayCollection();
        $this->propertyAssociations = new ArrayCollection();
        $this->entityAssociations = new ArrayCollection();
        $this->referencedNamespaceAssociations = new ArrayCollection();
        $this->childVersions = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->projectAssociations = new ArrayCollection();
        $this->classVersions = new ArrayCollection();
        $this->propertyVersions = new ArrayCollection();
        $this->containers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNamespaceURI()
    {
        return $this->namespaceURI;
    }

    public function getOriginalNamespaceURI()
    {
        return $this->originalNamespaceURI;
    }

    public function getClassPrefix()
    {
        return $this->classPrefix;
    }

    public function getPropertyPrefix()
    {
        return $this->propertyPrefix;
    }

    public function getImporterInteger()
    {
        return $this->importerInteger;
    }

    /**
     * @return ArrayCollection|OntoNamespace
     */
    public function getReferencedVersion()
    {
        return $this->referencedVersion;
    }

    /**
     * @return OntoNamespace
     */
    public function getTopLevelNamespace()
    {
        return $this->topLevelNamespace;
    }

    public function getIsTopLevelNamespace()
    {
        return $this->isTopLevelNamespace;
    }

    public function getIsExternalNamespace()
    {
        return $this->isExternalNamespace;
    }

    /**
     * @return bool
     */
    public function getIsOngoing()
    {
        return $this->isOngoing;
    }

    /**
     * @return bool
     */
    public function getHasPublication()
    {
        return $this->hasPublication;
    }

    /**
     * @return Project
     */
    public function getProjectForTopLevelNamespace()
    {
        return $this->projectForTopLevelNamespace;
    }

    public function getStandardLabel()
    {
        return $this->standardLabel;
    }

    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    public function getDeprecatedAt()
    {
        return $this->deprecatedAt;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function getModifier()
    {
        return $this->modifier;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function getModificationTime()
    {
        return $this->modificationTime;
    }

    /**
     * @return ArrayCollection|OntoClass[]
     */
    public function getClasses()
    {
        $classes = new ArrayCollection();

        foreach ($this->classVersions as $classVersion) {
            $classes->add($classVersion->getClass());
        }
        return $classes;
    }

    /**
     * @return ArrayCollection|Property[]
     */
    public function getProperties()
    {
        $properties = new ArrayCollection();

        foreach ($this->propertyVersions as $propertyVersion) {
            $properties->add($propertyVersion->getProperty());
        }

        return $properties;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return ArrayCollection|TextProperty[]
     */
    public function getTextProperties()
    {
        return $this->textProperties;
    }

    /**
     * @return ArrayCollection|ClassAssociation[]
     */
    public function getClassAssociations()
    {
        return $this->classAssociations;
    }

    public function getPropertyAssociations()
    {
        return $this->propertyAssociations;
    }

    /**
     * @return ArrayCollection|Project[]
     */
    public function getProfiles()
    {
        $profiles = new ArrayCollection();

        foreach ($this->referencedNamespaceAssociations as $referencedNamespaceAssociation) {
            if($referencedNamespaceAssociation->getProfile() !== null) {
                $profiles->add($referencedNamespaceAssociation->getProfile());
            }
        }
        return $profiles;
    }

    /**
     * @return ArrayCollection|Project[]
     */
    public function getProjects()
    {
        $projects = new ArrayCollection();

        foreach ($this->projectAssociations as $projectAssociation) {
            if($projectAssociation->getProject() !== null && !$projects->contains($projectAssociation->getProject())) {
                $projects->add($projectAssociation->getProject());
            }
        }
        return $projects;
    }

    /**
     * @return ArrayCollection|OntoNamespace[]
     */
    public function getChildVersions()
    {
        return $this->childVersions;
    }

    public function getIdentifierInNamespace()
    {
        return $this->namespaceURI;
    }

    /**
     * @return ArrayCollection|ProjectAssociation
     */
    public function getProjectAssociations()
    {
        return $this->projectAssociations;
    }

    /**
     * @return ArrayCollection
     */
    public function getClassVersions()
    {
        return $this->classVersions;
    }

    /**
     * @return ArrayCollection
     */
    public function getPropertyVersions()
    {
        return $this->propertyVersions;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return $this->standardLabel;
    }

    /**
     * @return ArrayCollection|ReferencedNamespaceAssociation[]
     */
    public function getReferencedNamespaceAssociations()
    {
        return $this->referencedNamespaceAssociations;
    }

    /**
     * @return ArrayCollection|OntoNamespace[] Retourne TOUS les espaces de noms référencés directs
     */
    public function getDirectReferencedNamespaces()
    {
        $referencedNamespaces = new ArrayCollection();
        foreach ($this->referencedNamespaceAssociations as $referencedNamespaceAssociation) {
            $referencedNamespaces->add($referencedNamespaceAssociation->getReferencedNamespace());
        }

        return $referencedNamespaces;
    }

    /**
     * @return ArrayCollection|OntoNamespace[] Retourne TOUS les espaces de noms référencés directs ET indirects
     */
    public function getAllReferencedNamespaces(?ArrayCollection $allReferencedNamespaces = null)
    {
        $allReferencedNamespaces ??= new ArrayCollection();
        if ($this->getDirectReferencedNamespaces()->isEmpty()) {
            return new ArrayCollection();
        }

        foreach ($this->getDirectReferencedNamespaces() as $directReferencedNamespace) {
            if (!$allReferencedNamespaces->contains($directReferencedNamespace)) {
                $allReferencedNamespaces->add($directReferencedNamespace);
                foreach ($directReferencedNamespace->getAllReferencedNamespaces($allReferencedNamespaces) as $ns) {
                    if (!$allReferencedNamespaces->contains($ns)) {
                        $allReferencedNamespaces->add($ns);
                    }
                }
            }
        }

        return $allReferencedNamespaces;
    }

    public function getRootNamespacePrefix()
    {
        if ($this->isTopLevelNamespace) {
            return $this->rootNamespacePrefix;
        }

        return false;
    }

    public function setRootNamespacePrefix($rootNamespacePrefix)
    {
        $this->rootNamespacePrefix = $rootNamespacePrefix;
    }

    public function setNamespaceURI($namespaceURI)
    {
        $this->namespaceURI = $namespaceURI;
    }

    public function setIsExternalNamespace($isExternalNamespace)
    {
        $this->isExternalNamespace = $isExternalNamespace;
    }

    public function setStandardLabel($standardLabel)
    {
        $this->standardLabel = $standardLabel;
    }

    public function setOriginalNamespaceURI($originalNamespaceURI)
    {
        $this->originalNamespaceURI = $originalNamespaceURI;
    }

    public function setClassPrefix($classPrefix)
    {
        $this->classPrefix = $classPrefix;
    }

    public function setPropertyPrefix($propertyPrefix)
    {
        $this->propertyPrefix = $propertyPrefix;
    }

    public function setReferencedVersion($referencedVersion)
    {
        $this->referencedVersion = $referencedVersion;
    }

    public function setTopLevelNamespace($topLevelNamespace)
    {
        $this->topLevelNamespace = $topLevelNamespace;
    }

    public function setIsTopLevelNamespace($isTopLevelNamespace)
    {
        $this->isTopLevelNamespace = $isTopLevelNamespace;
    }

    public function setIsOngoing($isOngoing)
    {
        $this->isOngoing = $isOngoing;
    }

    public function setHasPublication($hasPublication)
    {
        $this->hasPublication = $hasPublication;
    }

    public function setProjectForTopLevelNamespace($projectForTopLevelNamespace)
    {
        $this->projectForTopLevelNamespace = $projectForTopLevelNamespace;
    }

    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    public function setDeprecatedAt($deprecatedAt)
    {
        $this->deprecatedAt = $deprecatedAt;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function setModificationTime($modificationTime)
    {
        $this->modificationTime = $modificationTime;
    }

    public function setReferencedNamespaceAssociations($referencedNamespaceAssociations)
    {
        $this->referencedNamespaceAssociations = $referencedNamespaceAssociations;
    }

    /**
     * @param ArrayCollection $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * @param ArrayCollection $textProperties
     */
    public function setTextProperties($textProperties)
    {
        $this->textProperties = $textProperties;
    }

    public function addClassAssociation(ClassAssociation $classAssociation)
    {
        if ($this->classAssociations->contains($classAssociation)) {
            return;
        }
        $this->classAssociations[] = $classAssociation;
    }

    public function addPropertyAssociation(PropertyAssociation $propertyAssociation)
    {
        if ($this->propertyAssociations->contains($propertyAssociation)) {
            return;
        }
        $this->propertyAssociations[] = $propertyAssociation;
    }

    public function addEntityAssociation(EntityAssociation $entityAssociation)
    {
        if ($this->entityAssociations->contains($entityAssociation)) {
            return;
        }
        $this->entityAssociations[] = $entityAssociation;
    }

    public function addReferencedNamespaceAssociation(ReferencedNamespaceAssociation $referencedNamespaceAssociation)
    {
        if ($this->referencedNamespaceAssociations->contains($referencedNamespaceAssociation)) {
            return;
        }
        $this->referencedNamespaceAssociations[] = $referencedNamespaceAssociation;
    }

    public function addClassVersion(OntoClassVersion $classVersion)
    {
        if ($this->classVersions->contains($classVersion)) {
            return;
        }
        $this->classVersions[] = $classVersion;

        if (!$this->classes->contains($classVersion->getClass())) {
            $this->classes[] = $classVersion->getClass();
        }
    }

    public function addPropertyVersion(PropertyVersion $propertyVersion)
    {
        if ($this->propertyVersions->contains($propertyVersion)) {
            return;
        }
        $this->propertyVersions[] = $propertyVersion;

        if (!$this->properties->contains($propertyVersion->getProperty())) {
            $this->properties[] = $propertyVersion->getProperty();
        }
    }

    public function addReferenceNamespaceAssociation(ReferencedNamespaceAssociation $referencedNamespaceAssociation)
    {
        if ($this->referencedNamespaceAssociations->contains($referencedNamespaceAssociation)) {
            return;
        }
        $this->referencedNamespaceAssociations[] = $referencedNamespaceAssociation;
        // needed to update the owning side of the relationship!
        $referencedNamespaceAssociation->setNamespace($this);
    }

    public function addLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            return;
        }
        $this->labels[] = $label;
        // needed to update the owning side of the relationship!
        $label->setNamespace($this);
    }

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setNamespace($this);
    }

    public function __toString()
    {
        $s = $this->getStandardLabel();
        if (empty($s)) {
            $s = $this->namespaceURI;
        }

        return (string) $s;
    }

    /**
     * @return string the URI to be displayed
     */
    public function getDisplayURI()
    {
        $s = '';
        if (!empty($this->namespaceURI)) {
            $s = $this->namespaceURI;
        } else {
            $s = $this->getTopLevelNamespace()->getNamespaceURI();
        }

        return $s;
    }

    /**
     * @return array
     *               Utilisé pour le dropdown sélection espace de noms sur une classe / propriété / etc
     *               Peut être réutilisé sur une fonctionnalité similaire
     */
    public function getStatus()
    {
        if ($this->getIsOngoing()) {
            return ['classCss' => 'warning', 'label' => 'Ongoing'];
        } elseif (is_null($this->getDeprecatedAt())) {
            return ['classCss' => 'success', 'label' => 'Published'];
        }

        return ['classCss' => 'danger', 'label' => 'Deprecated'];
    }

    /**
     * @return int
     */
    public function getUriParameter()
    {
        return $this->uriParameter;
    }

    /**
     * @param int $uriParameter
     */
    public function setUriParameter($uriParameter)
    {
        $this->uriParameter = $uriParameter;
    }

    /**
     * @return array
     *               Retourne un tableau des id de ces namespaces suivants : ce namespace (this) et ses namespaces référencés, parents y compris
     */
    public function getSelectedNamespacesId()
    {
        // L'espace de noms 4 est systématiquement référencé par tous les NS c'est purement technique.
        $arrayIds[] = 4;

        $arrayIds[] = $this->getId();
        foreach ($this->getAllReferencedNamespaces() as $referencedNamespace) {
            $arrayIds[] = $referencedNamespace->getId();
        }

        // array_unique évite les doublons - utile si on veut compter combien de ns différents
        return array_values(array_unique($arrayIds));
    }

    /**
     * @return array
     *               Retourne un tableau des id de ces namespaces suivants : tous les namespaces dans le même root que ce namespace (this) et tous les namespaces dans le même root de ses namespaces référencés.
     *               Utilisé notamment pour répérer les entités qui n'existent pas dans une version mais dans une autre du même root
     */
    public function getLargeSelectedNamespacesId()
    {
        // L'espace de noms 4 est systématiquement référencé par tous les NS c'est purement technique.
        $arrayIds[] = 4;

        // Boucle sur le root du namespace d'origine :
        foreach ($this->getTopLevelNamespace()->getChildVersions() as $ns) {
            $arrayIds[] = $ns->getId();
        }

        foreach ($this->getReferencedNamespaceAssociations() as $referencedNamespaceAssociation) {
            foreach ($referencedNamespaceAssociation->getReferencedNamespace()->getTopLevelNamespace()->getChildVersions() as $ns) {
                $arrayIds[] = $ns->getId();
            }
        }

        // array_unique évite les doublons - utile si on veut compter combien de ns différents
        return array_values(array_unique($arrayIds));
    }

    /**
     * @return array
     *               Retourne un string pour indiquer le paramètre
     */
    public function getUriParameterStrings()
    {
        switch ($this->getUriParameter()) {
            case 0:
                $arrStr = ['label' => 'Entity identifier', 'classExample' => 'C1', 'propertyExample' => 'P1'];
                break;
            case 1:
                $arrStr = ['label' => 'Entity identifier + label', 'classExample' => 'C1_Class_Label', 'propertyExample' => 'P1_Property_Label'];
                break;
            case 2:
                $arrStr = ['label' => 'camelCase label', 'classExample' => 'classLabel', 'propertyExample' => 'propertyLabel'];
                break;
            case 3:
                $arrStr = ['label' => 'No parameter', 'classExample' => 'user_choice', 'propertyExample' => 'user_choice'];
                break;
            default:
                $arrStr = ['label' => 'Unknown parameter', 'classExample' => 'Unknown parameter', 'propertyExample' => 'Unknown parameter'];
                break;
        }

        return $arrStr;
    }

    public function addContainer(Container $container)
    {
        if ($this->containers->contains($container)) {
            return;
        }
        $this->containers[] = $container;
        // needed to update the owning side of the relationship!
        $container->addNamespace($this);
    }

    public function removeContainer(Container $container)
    {
        if (!$this->containers->contains($container)) {
            return;
        }
        $this->containers->removeElement($container);
        // needed to update the owning side of the relationship!
        $container->removeNamespace($this);
    }

    public function getContainers()
    {
        return $this->containers;
    }
}
