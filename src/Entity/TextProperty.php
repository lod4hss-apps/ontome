<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 11/07/2017
 * Time: 11:35.
 */

namespace App\Entity;

use App\Validator\Constraints\CharacterLength;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class TextProperty.
 */
#[ORM\Table(schema: 'che', name: 'text_property')]
#[ORM\Entity(repositoryClass: \App\Repository\TextPropertyRepository::class)]
#[Assert\GroupSequenceProvider]
class TextProperty implements GroupSequenceProviderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_text_property')]
    private $id;

    /**
     * @CharacterLength(
     *      groups={"Description"},
     *      min = 40,
     *      message = "Your description must be at least 40 characters long",
     * )
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private $textProperty;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private $languageIsoCode;

    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class, inversedBy: 'textProperties')]
    private $namespaceForVersion;

    #[ORM\JoinColumn(name: 'fk_associates_profile', referencedColumnName: 'pk_associates_profile', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \ProfileAssociation::class, inversedBy: 'textProperties')]
    private $profileAssociation;

    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: \OntoClass::class, inversedBy: 'textProperties')]
    #[Assert\Type(type: 'App\Entity\OntoClass')]
    #[Assert\Valid]
    private $class;

    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    #[ORM\ManyToOne(targetEntity: \Property::class, inversedBy: 'textProperties')]
    private $property;

    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace')]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class, inversedBy: 'textProperties')]
    private $namespace;

    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project')]
    #[ORM\ManyToOne(targetEntity: \Project::class, inversedBy: 'textProperties')]
    private $project;

    #[ORM\JoinColumn(name: 'fk_text_property_type', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class, inversedBy: 'textProperties')]
    #[Assert\Type(type: 'App\Entity\SystemType')]
    private $systemType;

    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class)]
    #[Assert\Type(type: 'App\Entity\SystemType')]
    private $validationStatus;

    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile')]
    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'textProperties')]
    #[Assert\Type(type: 'App\Entity\Profile')]
    #[Assert\Valid]
    private $profile;

    #[ORM\JoinColumn(name: 'fk_is_subclass_of', referencedColumnName: 'pk_is_subclass_of')]
    #[ORM\ManyToOne(targetEntity: \ClassAssociation::class, inversedBy: 'textProperties')]
    #[Assert\Type(type: 'App\Entity\ClassAssociation')]
    #[Assert\Valid]
    private $classAssociation;

    #[ORM\JoinColumn(name: 'fk_entity_association', referencedColumnName: 'pk_entity_association')]
    #[ORM\ManyToOne(targetEntity: \EntityAssociation::class, inversedBy: 'textProperties')]
    #[Assert\Type(type: 'App\Entity\EntityAssociation')]
    #[Assert\Valid]
    private $entityAssociation;

    #[ORM\JoinColumn(name: 'fk_is_subproperty_of', referencedColumnName: 'pk_is_subproperty_of')]
    #[ORM\ManyToOne(targetEntity: \PropertyAssociation::class, inversedBy: 'textProperties')]
    #[Assert\Type(type: 'App\Entity\PropertyAssociation')]
    #[Assert\Valid]
    private $propertyAssociation;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'textProperty')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private $comments;

    #[ORM\Column(type: 'text', nullable: true)]
    private $notes;

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

    #[ORM\JoinColumn(name: 'fk_entity_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $entityNamespaceForVersion;

    public function __construct()
    {
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

    public function getTextProperty()
    {
        return $this->textProperty;
    }

    public function getLanguageIsoCode()
    {
        return $this->languageIsoCode;
    }

    /**
     * @return OntoClass
     */
    public function getClass()
    {
        return $this->class;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @return ClassAssociation
     */
    public function getClassAssociation()
    {
        return $this->classAssociation;
    }

    /**
     * @return PropertyAssociation
     */
    public function getPropertyAssociation()
    {
        return $this->propertyAssociation;
    }

    /**
     * @return OntoClass|Property|OntoNamespace|null the object described by the text property
     */
    public function getObject()
    {
        $object = null;
        if (!is_null($this->class)) {
            $object = $this->class;
        } elseif (!is_null($this->property)) {
            $object = $this->property;
        } elseif (!is_null($this->namespace)) {
            $object = $this->namespace;
        } elseif (!is_null($this->classAssociation)) {
            $object = $this->classAssociation;
        } elseif (!is_null($this->propertyAssociation)) {
            $object = $this->propertyAssociation;
        } elseif (!is_null($this->entityAssociation)) {
            $object = $this->entityAssociation;
        } elseif (!is_null($this->profile)) {
            $object = $this->profile;
        } elseif (!is_null($this->project)) {
            $object = $this->project;
        }

        return $object;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    public function getProfileAssociation()
    {
        return $this->profileAssociation;
    }

    public function getValidationStatus()
    {
        return $this->validationStatus;
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
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return 'Text property n°'.$this->id;
    }

    public function setNamespaceForVersion($namespaceForVersion)
    {
        $this->namespaceForVersion = $namespaceForVersion;
    }

    public function setTextProperty($textProperty)
    {
        $this->textProperty = $textProperty;
    }

    public function setLanguageIsoCode($languageIsoCode)
    {
        $this->languageIsoCode = $languageIsoCode;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
    }

    public function setProfileAssociation($profileAssociation)
    {
        $this->profileAssociation = $profileAssociation;
    }

    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function setClassAssociation(?ClassAssociation $classAssociation = null)
    {
        $this->classAssociation = $classAssociation;
    }

    /**
     * @param PropertyAssociation $propertyAssociation
     */
    public function setPropertyAssociation($propertyAssociation)
    {
        $this->propertyAssociation = $propertyAssociation;
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

    public function getEntityAssociation()
    {
        return $this->entityAssociation;
    }

    public function setEntityAssociation($entityAssociation)
    {
        $this->entityAssociation = $entityAssociation;
    }

    public function getEntityNamespaceForVersion()
    {
        return $this->entityNamespaceForVersion;
    }

    public function setEntityNamespaceForVersion($entityNamespaceForVersion)
    {
        $this->entityNamespaceForVersion = $entityNamespaceForVersion;
    }

    /**
     * Returns which validation groups should be used for a certain state
     * of the object.
     *
     * @return array An array of validation groups
     */
    public function getGroupSequence()
    {
        if (!is_null($this->systemType) && $this->systemType->getId() == 16) {
            return ['TextProperty', 'Description'];
        }

        return ['TextProperty'];
    }
}
