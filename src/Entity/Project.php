<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 19/07/2017
 * Time: 16:19.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Project.
 */
#[ORM\Table(schema: 'che', name: 'project')]
#[ORM\UniqueConstraint(name: 'uniq_project_uri_project', columns: ['uri_project'])]
#[ORM\Entity]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_project')]
    private int $id;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $endDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'string', length: 500)]
    private string $standardLabel;

    #[ORM\ManyToOne(inversedBy: 'childProjects')]
    #[ORM\JoinColumn(name: 'fk_is_subproject_of', referencedColumnName: 'pk_project', nullable: true)]
    private ?Project $parentProject = null;

    #[ORM\OneToMany(targetEntity: Profile::class, mappedBy: 'projectOfBelonging')]
    private Collection $ownedProfiles;

    #[ORM\OneToMany(targetEntity: OntoNamespace::class, mappedBy: 'projectForTopLevelNamespace')]
    private Collection $managedNamespaces;

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

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'project', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $textProperties;

    #[Assert\Valid]
    #[Assert\NotBlank]
    #[ORM\OneToMany(targetEntity: Label::class, mappedBy: 'project', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $labels;

    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'parentProject')]
    private Collection $childProjects;

    #[Assert\NotBlank]
    #[ORM\OneToMany(targetEntity: UserProjectAssociation::class, mappedBy: 'project')]
    private Collection $userProjectAssociations;

    #[ORM\OneToMany(targetEntity: ProjectAssociation::class, mappedBy: 'project')]
    private Collection $projectAssociations;

    #[ORM\OneToMany(targetEntity: ProjectThesaurusAssociation::class, mappedBy: 'project')]
    private Collection $projectThesaurusAssociations;

    #[Assert\Url(message: 'Please enter a valid URI')]
    #[ORM\Column(nullable: true, unique: true)]
    private ?string $uriProject = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $project_types = [];

    #[ORM\OneToMany(targetEntity: Container::class, mappedBy: 'project')]
    private Collection $containers;

    public function __construct()
    {
        $this->ownedProfiles = new ArrayCollection();
        $this->managedNamespaces = new ArrayCollection();
        $this->textProperties = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->childProjects = new ArrayCollection();
        $this->userProjectAssociations = new ArrayCollection();
        $this->projectAssociations = new ArrayCollection();
        $this->projectThesaurusAssociations = new ArrayCollection();
        $this->project_types = [];
        $this->containers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getStandardLabel()
    {
        return $this->standardLabel;
    }

    public function getParentProject()
    {
        return $this->parentProject;
    }

    /**
     * @return ArrayCollection|Project[]
     */
    public function getChildProjects()
    {
        return $this->childProjects;
    }

    /**
     * @return ArrayCollection|ProjectThesaurusAssociation[]
     */
    public function getProjectThesaurusAssociations()
    {
        return $this->projectThesaurusAssociations;
    }

    /**
     * @return User
     */
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
    public function getOwnedProfiles()
    {
        return $this->ownedProfiles;
    }

    /**
     * @return ArrayCollection|OntoNamespace[]
     */
    public function getManagedNamespaces()
    {
        return $this->managedNamespaces;
    }

    /**
     * @return ArrayCollection|Label[]
     */
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
     * @return ArrayCollection|Profile[]
     */
    public function getProfiles()
    {
        $profiles = [];
        $projectAssociationsWithValidProfile = $this->projectAssociations->filter(fn($projectAssociation) => $projectAssociation->getProfile() !== null);
        foreach ($projectAssociationsWithValidProfile as $projectAssociationWithValidProfile) {
            $profiles[] = $projectAssociationWithValidProfile->getProfile();
        }
        return $profiles;
    }

    /**
     * @return ArrayCollection|OntoNamespace[]
     */
    public function getNamespaces()
    {
        $namespaces = [];
        $projectAssociationsWithValidNamespace = $this->projectAssociations->filter(fn($projectAssociation) => $projectAssociation->getNamespace() !== null);
        foreach ($projectAssociationsWithValidNamespace as $projectAssociationWithValidNamespace) {
            $namespaces[] = $projectAssociationWithValidNamespace->getNamespace();
        }
        return $namespaces;
    }

    /**
     * @return ArrayCollection|UserProjectAssociation
     */
    public function getUserProjectAssociations()
    {
        return $this->userProjectAssociations;
    }

    /**
     * @return ArrayCollection|ProjectAssociation[]
     */
    public function getProjectAssociations()
    {
        return $this->projectAssociations;
    }

    /**
     * @return int|null
     */
    public function getPermissionForUser(?User $user = null)
    {
        foreach ($this->getUserProjectAssociations() as $userProjectAssociation) {
            if ($userProjectAssociation->getUser() === $user) {
                return $userProjectAssociation->getPermission();
            }
        }

        return null;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return $this->standardLabel;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    public function setTextProperties($textProperties)
    {
        $this->textProperties = $textProperties;
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

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setProject($this);
    }

    public function addLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            return;
        }
        $this->labels[] = $label;
        // needed to update the owning side of the relationship!
        $label->setProject($this);
    }

    /**
     * @return array
     */
    public function getProjectTypes()
    {
        return $this->project_types ?: [];
    }

    public function setProjectTypes(?array $projectTypes = null)
    {
        $this->project_types = $projectTypes ?: [];
    }

    public function __toString()
    {
        $s = $this->getStandardLabel();

        return (string) $s;
    }

    public function getUriProject()
    {
        return $this->uriProject;
    }

    public function setUriProject($uriProject)
    {
        $this->uriProject = $uriProject;
    }

    public function addContainer(Container $container)
    {
        if ($this->containers->contains($container)) {
            return;
        }
        $this->containers[] = $container;
        // needed to update the owning side of the relationship!
        $container->setProject($this);
    }

    public function removeContainer(Container $container)
    {
        if (!$this->containers->contains($container)) {
            return;
        }
        $this->containers->removeElement($container);
    }

    public function getContainers()
    {
        return $this->containers;
    }
}
