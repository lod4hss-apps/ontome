<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 09/01/2018
 * Time: 14:54.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ClassAssociation.
 */
#[ORM\Table(schema: 'che', name: 'is_subclass_of')]
#[ORM\Entity]
#[UniqueEntity(fields: ['childClass', 'parentClass', 'namespaceForVersion'], message: 'This parent class is already associated with this child class')]
class ClassAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_is_subclass_of')]
    private $id;

    #[ORM\JoinColumn(name: 'is_child_class', referencedColumnName: 'pk_class')]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \OntoClass::class, inversedBy: 'childClassAssociations')]
    private $childClass;

    #[ORM\JoinColumn(name: 'fk_child_class_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $childClassNamespace;

    #[ORM\JoinColumn(name: 'is_parent_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: \OntoClass::class, inversedBy: 'parentClassAssociations')]
    private $parentClass;

    #[ORM\JoinColumn(name: 'fk_parent_class_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $parentClassNamespace;

    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class, inversedBy: 'classAssociations')]
    private $namespaceForVersion;

    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class)]
    #[Assert\Type(type: 'App\Entity\SystemType')]
    private $validationStatus;

    #[ORM\Column(type: 'text')]
    private $notes;

    #[Assert\NotNull]
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'classAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private $textProperties;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'classAssociation')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private $comments;

    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \User::class)]
    private $creator;

    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \User::class)]
    private $modifier;

    #[ORM\Column(type: 'datetime')]
    private $creationTime;

    #[ORM\Column(type: 'datetime')]
    private $modificationTime;

    /**
     * ClassAssociation constructor.
     */
    public function __construct()
    {
        $this->textProperties = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return OntoNamespace
     */
    public function getNamespaceForVersion()
    {
        return $this->namespaceForVersion;
    }

    public function getChildClass()
    {
        return $this->childClass;
    }

    #[Assert\NotBlank]
    public function getParentClass()
    {
        return $this->parentClass;
    }

    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return ArrayCollection|TextProperty[]
     */
    public function getTextProperties()
    {
        return $this->textProperties;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return 'Class association n°'.$this->id;
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

    public function getChildClassNamespace()
    {
        return $this->childClassNamespace;
    }

    public function getParentClassNamespace()
    {
        return $this->parentClassNamespace;
    }

    public function setNamespaceForVersion($namespaceForVersion)
    {
        $this->namespaceForVersion = $namespaceForVersion;
    }

    public function setChildClass($childClass)
    {
        $this->childClass = $childClass;
    }

    public function setTextProperties($textProperties)
    {
        $this->textProperties = $textProperties;
    }

    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
    }

    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
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

    public function setChildClassNamespace($childClassNamespace)
    {
        $this->childClassNamespace = $childClassNamespace;
    }

    public function setParentClassNamespace($parentClassNamespace)
    {
        $this->parentClassNamespace = $parentClassNamespace;
    }

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setClassAssociation($this);
    }

    public function __toString()
    {
        return (string) $this->childClass->getClassVersionForDisplay().': parent class association';
    }
}
