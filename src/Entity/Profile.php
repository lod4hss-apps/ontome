<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 16/11/2017
 * Time: 11:34.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Property;
use App\Entity\User;
use App\Entity\TextProperty;
use App\Entity\Label;
use App\Entity\EntityUserProjectAssociation;
use App\Entity\ProjectAssociation;
use App\Entity\ProfileAssociation;
use App\Entity\ReferencedNamespaceAssociation;

/**
 * Class Profile.
 */
#[ORM\Table(schema: 'che', name: 'profile')]
#[ORM\Index(name: 'profile_fk_root_profile_idx', columns: ['fk_root_profile'])]
#[ORM\Entity]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_profile')]
    private int $id;

    #[ORM\Column(length: 500)]
    private string $standardLabel;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $endDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $wasClosedAt = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isOngoing = true;

    #[ORM\Column(options: ['default' => false])]
    private bool $isVisible = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $isRootProfile = false;

    #[ORM\Column(nullable: true)]
    private ?int $version = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isForcedPublication = false;

    #[ORM\ManyToOne(inversedBy: 'childProfiles')]
    #[ORM\JoinColumn(name: 'fk_root_profile', referencedColumnName: 'pk_profile', nullable: true)]
    private Profile $rootProfile;

    #[ORM\ManyToOne(inversedBy: 'ownedProfiles')]
    #[ORM\JoinColumn(name: 'fk_project_of_belonging', referencedColumnName: 'pk_project')]
    private Project $projectOfBelonging;

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

    #[ORM\OneToMany(targetEntity: Profile::class, mappedBy: 'rootProfile')]
    private Collection $childProfiles;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'profile', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $textProperties;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: Label::class, mappedBy: 'profile', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $labels;

    #[ORM\OneToMany(targetEntity: ProjectAssociation::class, mappedBy: 'profile')]
    private Collection $projectAssociations;

    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProfileAssociation::class, mappedBy: 'profile', cascade: ['persist'])]
    #[ORM\OrderBy(['systemType' => 'ASC'])]
    private Collection $profileAssociations;

    #[ORM\OneToMany(targetEntity: EntityUserProjectAssociation::class, mappedBy: 'profile')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $profileUserProjectAssociation;

    #[ORM\OneToMany(targetEntity: ReferencedNamespaceAssociation::class, mappedBy: 'profile')]
    private Collection $referencedNamespaceAssociations;

    public function __construct()
    {
        $this->childProfiles = new ArrayCollection();
        $this->textProperties = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->projectAssociations = new ArrayCollection();
        $this->profileAssociations = new ArrayCollection();
        $this->profileUserProjectAssociation = new ArrayCollection();
        $this->referencedNamespaceAssociations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStandardLabel()
    {
        return $this->standardLabel;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getWasClosedAt()
    {
        return $this->wasClosedAt;
    }

    public function getIsOngoing()
    {
        return $this->isOngoing;
    }

    public function getIsForcedPublication()
    {
        return $this->isForcedPublication;
    }

    public function getIsRootProfile()
    {
        return $this->isRootProfile;
    }

    /**
     * @return Profile
     */
    public function getRootProfile()
    {
        if ($this->isRootProfile) {
            return $this;
        }

        return $this->rootProfile;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
     * @return ArrayCollection|Profile[]
     */
    public function getChildProfiles()
    {
        return $this->childProfiles;
    }

    /**
     * @return ArrayCollection|TextProperty[]
     */
    public function getTextProperties()
    {
        return $this->textProperties;
    }

    /**
     * @return ArrayCollection|Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return Project
     */
    public function getProjectOfBelonging()
    {
        if ($this->isRootProfile) {
            return $this->projectOfBelonging;
        }

        return $this->getRootProfile()->getProjectOfBelonging();
    }

    /**
     * @return ArrayCollection|OntoClass[]
     */
    public function getClasses()
    {
        $classes = [];
        // Filter the profileAssociations where the class is defined
        $profileAssociationsWithValidClass = $this->profileAssociations->filter(fn($profileAssociation) => $profileAssociation->getClass() !== null);
        foreach ($profileAssociationsWithValidClass as $profileAssociationWithValidClass) {
            $classes[] = $profileAssociationWithValidClass->getClass();
        }
        return $classes;
    }

    /**
     * @return ArrayCollection|Property[]
     */
    public function getProperties()
    {
        $properties = [];
        // Filter the profileAssociations where the property is defined
        $profileAssociationsWithValidProperty = $this->profileAssociations->filter(fn($profileAssociation) => $profileAssociation->getProperty() !== null);
        foreach ($profileAssociationsWithValidProperty as $profileAssociationWithValidProperty) {
            $properties[] = $profileAssociationWithValidProperty->getProperty();
        }
        return $properties;
    }

    /**
     * @return ArrayCollection|Project[]
     */
    public function getProjects()
    {
        $projects = [];
        foreach ($this->projectAssociations as $projectAssociation) {
            if ($projectAssociation->getProject() !== null) {
                $projects[] = $projectAssociation->getProject();
            }
        }
        return $projects;
    }

    /**
     * @return ArrayCollection|OntoNamespace[]
     */
    public function getNamespaces()
    {
        $namespaces = new ArrayCollection();
        foreach ($this->referencedNamespaceAssociations as $referencedNamespaceAssociation) {
            if ($referencedNamespaceAssociation->getReferencedNamespace() !== null) {
                $namespaces->add($referencedNamespaceAssociation->getReferencedNamespace());
            }
        }
        return $namespaces;
    }

    public function getProfileAssociations()
    {
        return $this->profileAssociations;
    }

    public function addProfileAssociation($profileAssociation)
    {
        if ($this->profileAssociations->contains($profileAssociation)) {
            return;
        }
        $this->profileAssociations[] = $profileAssociation;
        // needed to update the owning side of the relationship!
        $profileAssociation->setProfile($this);
    }

    public function getProfileUserProjectAssociation()
    {
        return $this->profileUserProjectAssociation;
    }

    public function setProfileUserProjectAssociation($profileUserProjectAssociation)
    {
        $this->profileUserProjectAssociation = $profileUserProjectAssociation;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return $this->standardLabel;
    }

    /**
     * @return ArrayCollection|OntoNamespace[] Retourne TOUS les espaces de noms référencés directs ET indirects
     */
    public function getAllReferencedNamespaces(?ArrayCollection $allReferencedNamespaces = null)
    {
        $allReferencedNamespaces ??= new ArrayCollection();
        if ($this->getNamespaces()->isEmpty()) {
            return new ArrayCollection();
        }

        foreach ($this->getNamespaces() as $directReferencedNamespace) {
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

    public function setStandardLabel($standardLabel)
    {
        $this->standardLabel = $standardLabel;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function setWasClosedAt($wasClosedAt)
    {
        $this->wasClosedAt = $wasClosedAt;
    }

    /**
     * @param bool $isOngoing
     */
    public function setIsOngoing($isOngoing)
    {
        $this->isOngoing = $isOngoing;
    }

    /**
     * @param bool $isRootProfile
     */
    public function setIsRootProfile($isRootProfile)
    {
        $this->isRootProfile = $isRootProfile;
    }

    public function setIsForcedPublication($isForcedPublication)
    {
        $this->isForcedPublication = $isForcedPublication;
    }

    /**
     * @param Profile $rootProfile
     */
    public function setRootProfile(Profile $rootProfile)
    {
        $this->rootProfile = $rootProfile;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param Project $projectOfBelonging
     */
    public function setProjectOfBelonging(Project $projectOfBelonging)
    {
        $this->projectOfBelonging = $projectOfBelonging;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
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

    public function setTextProperties($textProperties)
    {
        $this->textProperties = $textProperties;
    }

    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setProfile($this);
    }

    public function addLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            return;
        }
        $this->labels[] = $label;
        // needed to update the owning side of the relationship!
        $label->setProfile($this);
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

    public function __toString()
    {
        return $this->standardLabel;
    }

    public function isPublishable()
    {
        if ($this->isRootProfile) {
            return false;
        }
        $profileNamespaces = clone $this->getNamespaces();
        foreach ($this->getNamespaces() as $ns) {
            foreach ($ns->getAllReferencedNamespaces() as $nsRef) {
                if (!$profileNamespaces->contains($nsRef)) {
                    $profileNamespaces->add($nsRef);
                }
            }
        }
        foreach ($this->getProfileAssociations() as $profileAssociation) {
            if ($profileAssociation->getSystemType()->getId() == 5
                // Refuser la publication s'il y a des entités qui ne sont pas dans les namespaces reférencés du profil
                && !$profileNamespaces->contains($profileAssociation->getEntityNamespaceForVersion())) {
                return false;
            }
        }

        return true;
    }
}
