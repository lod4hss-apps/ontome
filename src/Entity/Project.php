<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 19/07/2017
 * Time: 16:19.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Project.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 *
 * @ORM\Table(schema="che", name="project")
 */
class Project
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer", name="pk_project")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @ORM\Column(type="text")
     */
    private $standardLabel;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="childProjects")
     *
     * @ORM\JoinColumn(name="fk_is_subproject_of", referencedColumnName="pk_project", nullable=true)
     */
    private $parentProject;

    /**
     * @ORM\OneToMany(targetEntity="Profile", mappedBy="projectOfBelonging")
     */
    private $ownedProfiles;

    /**
     * @ORM\OneToMany(targetEntity="OntoNamespace", mappedBy="projectForTopLevelNamespace")
     */
    private $managedNamespaces;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @ORM\JoinColumn(name="creator", referencedColumnName="pk_user", nullable=false)
     */
    private $creator;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @ORM\JoinColumn(name="modifier", referencedColumnName="pk_user", nullable=false)
     */
    private $modifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modificationTime;

    /**
     * @ORM\ManyToMany(targetEntity="Profile",  inversedBy="Project", fetch="EXTRA_LAZY")
     *
     * @ORM\JoinTable(schema="che", name="associates_project",
     *      joinColumns={@ORM\JoinColumn(name="fk_project", referencedColumnName="pk_project")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="fk_profile", referencedColumnName="pk_profile")}
     *      )
     */
    private $profiles;

    /**
     * @ORM\ManyToMany(targetEntity="OntoNamespace",  inversedBy="projects", fetch="EXTRA_LAZY")
     *
     * @ORM\JoinTable(schema="che", name="associates_project",
     *      joinColumns={@ORM\JoinColumn(name="fk_project", referencedColumnName="pk_project")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="fk_namespace", referencedColumnName="pk_namespace")}
     *      )
     */
    private $namespaces;

    /**
     * @Assert\Valid()
     *
     * @Assert\NotNull()
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TextProperty", mappedBy="project", cascade={"persist"})
     *
     * @ORM\OrderBy({"languageIsoCode" = "ASC"})
     */
    private $textProperties;

    /**
     * @Assert\Valid()
     *
     * @Assert\NotBlank()
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Label", mappedBy="project", cascade={"persist"})
     *
     * @ORM\OrderBy({"languageIsoCode" = "ASC"})
     */
    private $labels;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentProject")
     */
    private $childProjects;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\OneToMany(targetEntity="UserProjectAssociation", mappedBy="project")
     */
    private $userProjectAssociations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectAssociation", mappedBy="project")
     */
    private $projectAssociations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectThesaurusAssociation", mappedBy="project")
     */
    private $projectThesaurusAssociations;

    /**
     * @Assert\Url(message="Please enter a valid URI")
     *
     * @ORM\Column(type="text", nullable=true, unique=true)
     */
    private $uriProject;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $project_types = [];

    /**
     * @ORM\OneToMany(targetEntity="Container", mappedBy="project")
     */
    private $containers;

    public function __construct()
    {
        $this->ownedProfiles = new ArrayCollection();
        $this->managedNamespaces = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $this->namespaces = new ArrayCollection();
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
     * @return ArrayCollection|Container[]
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
        return $this->profiles;
    }

    /**
     * @return ArrayCollection|OntoNamespace[]
     */
    public function getNamespaces()
    {
        return $this->namespaces;
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
     * @return int
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
