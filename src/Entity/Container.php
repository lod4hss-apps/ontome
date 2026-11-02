<?php

/**
 * Created by .
 * User: Alexandre
 * Date: 19/02/2026
 * Time: 10:40.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Container.
 */
#[ORM\Table(schema: 'che', name: 'container')]
#[ORM\Index(name: 'container_fk_project_idx', columns: ['fk_project'])]
#[ORM\Index(name: 'container_fk_label_idx', columns: ['fk_label'])]
#[ORM\Index(name: 'container_creator_idx', columns: ['creator'])]
#[ORM\Index(name: 'container_modifier_idx', columns: ['modifier'])]
#[ORM\Entity]
class Container
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_container')]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'containers')]
    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    private Project $project;

    #[ORM\ManyToMany(targetEntity: OntoNamespace::class, inversedBy: 'containers')]
    #[ORM\JoinTable(schema: 'che', name: 'associates_container_namespace')]
    #[ORM\JoinColumn(name: 'fk_container', referencedColumnName: 'pk_container')]
    #[ORM\InverseJoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace')]
    private Collection $namespaces;

    #[ORM\OneToMany(targetEntity: Pathbuilder::class, mappedBy: 'container')]
    private $pathbuilders;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_label', referencedColumnName: 'pk_label')]
    private Label $label;

    #[ORM\Column(options: ['default' => true])]
    private bool $isOngoing;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    private User $creator;

    #[Assert\NotBlank]
    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    private User $modifier;

    #[ORM\Column(options: ['default' => 'now'])]
    private DateTime $creationTime;

    #[ORM\Column]
    private DateTime $modificationTime;

    public function __construct()
    {
        $this->namespaces = new ArrayCollection();
        $this->pathbuilders = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getIsOngoing()
    {
        return $this->isOngoing;
    }

    public function setIsOngoing($isOngoing)
    {
        $this->isOngoing = $isOngoing;
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

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function addNamespace($namespace)
    {
        if (!$this->namespaces->contains($namespace)) {
            $this->namespaces->add($namespace);
        }
    }

    public function removeNamespace($namespace)
    {
        if ($this->namespaces->contains($namespace)) {
            $this->namespaces->removeElement($namespace);
        }
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function addPathbuilder($pathbuilder)
    {
        if (!$this->pathbuilders->contains($pathbuilder)) {
            $this->pathbuilders->add($pathbuilder);
        }
    }

    public function removePathbuilder($pathbuilder)
    {
        if ($this->pathbuilders->contains($pathbuilder)) {
            $this->pathbuilders->removeElement($pathbuilder);
        }
    }

    public function getPathbuilders()
    {
        return $this->pathbuilders;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return User
     */
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
}
