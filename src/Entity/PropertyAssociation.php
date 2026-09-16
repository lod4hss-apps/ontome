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
 * Class PropertyAssociation.
 */
#[ORM\Table(schema: 'che', name: 'is_subproperty_of')]
#[ORM\Entity]
#[UniqueEntity(fields: ['childProperty', 'parentProperty', 'namespaceForVersion'], message: 'This parent property is already associated with this child property')]
class PropertyAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_is_subproperty_of')]
    private $id;

    #[ORM\JoinColumn(name: 'is_child_property', referencedColumnName: 'pk_property')]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \Property::class, inversedBy: 'childPropertyAssociations')]
    private $childProperty;

    #[ORM\JoinColumn(name: 'fk_child_property_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $childPropertyNamespace;

    #[ORM\JoinColumn(name: 'is_parent_property', referencedColumnName: 'pk_property')]
    #[ORM\ManyToOne(targetEntity: \Property::class, inversedBy: 'parentPropertyAssociations')]
    private $parentProperty;

    #[ORM\JoinColumn(name: 'fk_parent_property_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $parentPropertyNamespace;

    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class, inversedBy: 'propertyAssociations')]
    private $namespaceForVersion;

    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class)]
    #[Assert\Type(type: 'App\Entity\SystemType')]
    private $validationStatus;

    #[ORM\Column(type: 'text')]
    private $notes;

    #[Assert\NotNull]
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'propertyAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private $textProperties;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'propertyAssociation')]
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
     * PropertyAssociation constructor.
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

    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    public function getChildProperty()
    {
        return $this->childProperty;
    }

    #[Assert\NotBlank]
    public function getParentProperty()
    {
        return $this->parentProperty;
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
        return 'Property association n°'.$this->id;
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

    public function getChildPropertyNamespace()
    {
        return $this->childPropertyNamespace;
    }

    public function setChildPropertyNamespace($childPropertyNamespace)
    {
        $this->childPropertyNamespace = $childPropertyNamespace;
    }

    public function setNamespaceForVersion($namespaceForVersion)
    {
        $this->namespaceForVersion = $namespaceForVersion;
    }

    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
    }

    public function setChildProperty($childProperty)
    {
        $this->childProperty = $childProperty;
    }

    public function setTextProperties($textProperties)
    {
        $this->textProperties = $textProperties;
    }

    public function setParentProperty($parentProperty)
    {
        $this->parentProperty = $parentProperty;
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

    public function getParentPropertyNamespace()
    {
        return $this->parentPropertyNamespace;
    }

    public function setParentPropertyNamespace($parentPropertyNamespace)
    {
        $this->parentPropertyNamespace = $parentPropertyNamespace;
    }

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setPropertyAssociation($this);
    }

    public function __toString()
    {
        return (string) $this->childProperty->getPropertyVersionForDisplay().': parent property association';
    }
}
