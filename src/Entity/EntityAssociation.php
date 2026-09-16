<?php

/**
 * Created by PhpStorm.
 * User: pc-alexandre-pro
 * Date: 07/05/2019
 * Time: 14:45.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EntityAssociation.
 */
#[ORM\Table(name: 'che.entity_association')]
#[ORM\Entity]
class EntityAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_entity_association')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_source_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: \OntoClass::class, inversedBy: 'sourceEntityAssociations')]
    private $sourceClass;

    #[ORM\JoinColumn(name: 'fk_target_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: \OntoClass::class, inversedBy: 'targetEntityAssociations')]
    private $targetClass;

    #[ORM\JoinColumn(name: 'fk_source_property', referencedColumnName: 'pk_property')]
    #[ORM\ManyToOne(targetEntity: \Property::class, inversedBy: 'sourceEntityAssociations')]
    private $sourceProperty;

    #[ORM\JoinColumn(name: 'fk_source_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $sourceNamespaceForVersion;

    #[ORM\JoinColumn(name: 'fk_target_property', referencedColumnName: 'pk_property')]
    #[ORM\ManyToOne(targetEntity: \Property::class, inversedBy: 'targetEntityAssociations')]
    private $targetProperty;

    #[ORM\JoinColumn(name: 'fk_target_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $targetNamespaceForVersion;

    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class, inversedBy: 'entityAssociations')]
    private $systemType;

    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class)]
    #[Assert\Type(type: 'App\Entity\SystemType')]
    private $validationStatus;

    #[ORM\Column(type: 'boolean', name: 'directed')]
    private $directed;

    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class, inversedBy: 'entityAssociations')]
    private $namespaceForVersion;

    #[Assert\NotNull]
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'entityAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private $textProperties;

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

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'entityAssociation')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private $comments;

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
