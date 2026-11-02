<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 09/01/2018
 * Time: 14:54.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PropertyAssociation.
 */
#[ORM\Table(schema: 'che', name: 'is_subproperty_of')]
#[ORM\Index(name: 'is_subproperty_of_fk_child_property_namespace_idx', columns: ['fk_child_property_namespace'])]
#[ORM\Index(name: 'is_subproperty_of_fk_parent_property_namespace_idx', columns: ['fk_parent_property_namespace'])]
#[ORM\Index(name: 'is_subproperty_of_fk_namespace_for_version_idx', columns: ['fk_namespace_for_version'])]
#[ORM\Entity]
#[UniqueEntity(fields: ['childProperty', 'parentProperty', 'namespaceForVersion'], message: 'This parent property is already associated with this child property')]
class PropertyAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_is_subproperty_of')]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(inversedBy: 'childPropertyAssociations')]
    #[ORM\JoinColumn(name: 'is_child_property', referencedColumnName: 'pk_property')]
    private Property $childProperty;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_child_property_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $childPropertyNamespace;

    #[ORM\ManyToOne(inversedBy: 'parentPropertyAssociations')]
    #[ORM\JoinColumn(name: 'is_parent_property', referencedColumnName: 'pk_property')]
    private Property $parentProperty;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_parent_property_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $parentPropertyNamespace;

    #[ORM\ManyToOne(inversedBy: 'propertyAssociations')]
    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $namespaceForVersion;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    private SystemType $validationStatus;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[Assert\NotNull]
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'propertyAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $textProperties;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'propertyAssociation')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $comments;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    private User $creator;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    private User $modifier;

    #[ORM\Column(options: ['default' => 'now'])]
    private DateTime $creationTime;

    #[ORM\Column]
    private DateTime $modificationTime;

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
