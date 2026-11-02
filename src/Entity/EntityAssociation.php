<?php

/**
 * Created by PhpStorm.
 * User: pc-alexandre-pro
 * Date: 07/05/2019
 * Time: 14:45.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EntityAssociation.
 */
#[ORM\Table(name: 'che.entity_association')]
#[ORM\Index(name: 'entity_association_fk_source_namespace_for_version_idx', columns: ['fk_source_namespace_for_version'])]
#[ORM\Index(name: 'entity_association_fk_target_namespace_for_version_idx', columns: ['fk_target_namespace_for_version'])]
#[ORM\Index(name: 'entity_association_fk_namespace_for_version_idx', columns: ['fk_namespace_for_version'])]
#[ORM\Index(name: 'entity_association_fk_system_type_idx', columns: ['fk_system_type'])]
#[ORM\Entity]
class EntityAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_entity_association')]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'sourceEntityAssociations')]
    #[ORM\JoinColumn(name: 'fk_source_class', referencedColumnName: 'pk_class')]
    private OntoClass $sourceClass;

    #[ORM\ManyToOne(inversedBy: 'targetEntityAssociations')]
    #[ORM\JoinColumn(name: 'fk_target_class', referencedColumnName: 'pk_class')]
    private OntoClass $targetClass;

    #[ORM\ManyToOne(inversedBy: 'sourceEntityAssociations')]
    #[ORM\JoinColumn(name: 'fk_source_property', referencedColumnName: 'pk_property')]
    private Property $sourceProperty;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_source_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $sourceNamespaceForVersion;

    #[ORM\ManyToOne(inversedBy: 'targetEntityAssociations')]
    #[ORM\JoinColumn(name: 'fk_target_property', referencedColumnName: 'pk_property')]
    private Property $targetProperty;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_target_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $targetNamespaceForVersion;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type')]
    private SystemType $systemType;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    private SystemType $validationStatus;

    #[ORM\Column(options: ['default' => false])]
    private bool $directed;

    #[ORM\ManyToOne(inversedBy: 'entityAssociations')]
    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $namespaceForVersion;

    #[Assert\NotNull]
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'entityAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $textProperties;

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

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'entityAssociation')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * EntityAssociation constructor.
     */
    public function __construct()
    {
        $this->textProperties = new ArrayCollection();
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

    public function getSourceClass()
    {
        return $this->sourceClass;
    }

    public function getTargetClass()
    {
        return $this->targetClass;
    }

    public function getSourceProperty()
    {
        return $this->sourceProperty;
    }

    public function getTargetProperty()
    {
        return $this->targetProperty;
    }

    public function getSystemType()
    {
        return $this->systemType;
    }

    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    public function getDirected()
    {
        return $this->directed;
    }

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
     * @return mixed OntoClass or Property
     */
    public function getSource()
    {
        $source = null;
        if (!is_null($this->getSourceClass())) {
            $source = $this->getSourceClass();
        } elseif (!is_null($this->getSourceProperty())) {
            $source = $this->getSourceProperty();
        }

        return $source;
    }

    public function getTarget()
    {
        $target = null;
        if (!is_null($this->getTargetClass())) {
            $target = $this->getTargetClass();
        }

        if (!is_null($this->getTargetProperty())) {
            $target = $this->getTargetProperty();
        }

        return $target;
    }

    /**
     * return String the source object's type.
     */
    public function getSourceObjectType()
    {
        $objectType = null;
        if (!is_null($this->getSourceClass())) {
            $objectType = 'class';
        }
        if (!is_null($this->getSourceProperty())) {
            $objectType = 'property';
        }

        return $objectType;
    }

    /**
     * return String the source object's type.
     */
    public function getTargetObjectType()
    {
        $objectType = null;
        if (!is_null($this->getTargetClass())) {
            $objectType = 'class';
        }
        if (!is_null($this->getTargetProperty())) {
            $objectType = 'property';
        }

        return $objectType;
    }

    public function getSourceNamespaceForVersion()
    {
        return $this->sourceNamespaceForVersion;
    }

    public function getTargetNamespaceForVersion()
    {
        return $this->targetNamespaceForVersion;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    public function setNamespaceForVersion($namespaceForVersion)
    {
        $this->namespaceForVersion = $namespaceForVersion;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setSourceClass($sourceClass)
    {
        $this->sourceClass = $sourceClass;
    }

    public function setTargetClass($targetClass)
    {
        $this->targetClass = $targetClass;
    }

    public function setSourceProperty($sourceProperty)
    {
        $this->sourceProperty = $sourceProperty;
    }

    public function setTargetProperty($targetProperty)
    {
        $this->targetProperty = $targetProperty;
    }

    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
    }

    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
    }

    public function setDirected($directed)
    {
        $this->directed = $directed;
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

    public function setSourceNamespaceForVersion($sourceNamespaceForVersion)
    {
        $this->sourceNamespaceForVersion = $sourceNamespaceForVersion;
    }

    public function setTargetNamespaceForVersion($targetNamespaceForVersion)
    {
        $this->targetNamespaceForVersion = $targetNamespaceForVersion;
    }

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setEntityAssociation($this);
    }

    public function __toString()
    {
        if (!is_null($this->sourceClass)) {
            return (string) $this->sourceClass->getClassVersionForDisplay().' - '.$this->targetClass->getClassVersionForDisplay().' : '.$this->systemType;
        } elseif (!is_null($this->sourceProperty)) {
            return (string) $this->sourceProperty->getPropertyVersionForDisplay().' - '.$this->targetProperty->getPropertyVersionForDisplay().' : '.$this->systemType;
        }
    }
}
