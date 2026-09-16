<?php

/**
 * Created by .
 * User: Alexandre
 * Date: 19/02/2026
 * Time: 10:40.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Container.
 */
#[ORM\Table(schema: 'che', name: 'container')]
#[ORM\Entity(repositoryClass: \App\Repository\ContainerRepository::class)]
class Container
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_container')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Project::class, inversedBy: 'containers')]
    private $project;

    #[ORM\JoinTable(schema: 'che', name: 'associates_container_namespace')]
    #[ORM\JoinColumn(name: 'fk_container', referencedColumnName: 'pk_container')]
    #[ORM\InverseJoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace')]
    #[ORM\ManyToMany(targetEntity: \OntoNamespace::class, inversedBy: 'containers')]
    private $namespaces;

    #[ORM\OneToMany(targetEntity: \Pathbuilder::class, mappedBy: 'container')]
    private $pathbuilders;

    #[ORM\JoinColumn(name: 'fk_label', referencedColumnName: 'pk_label')]
    #[ORM\ManyToOne(targetEntity: \Label::class, inversedBy: 'containers')]
    private $label;

    #[ORM\Column(type: 'boolean')]
    private $isOngoing;

    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \User::class)]
    private $creator;

    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \User::class)]
    private $modifier;

    #[ORM\Column(type: 'datetime')]
    private $creationTime;

    #[ORM\Column(type: 'datetime')]
    private $modificationTime;

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
