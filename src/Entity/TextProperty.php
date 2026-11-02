<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 11/07/2017
 * Time: 11:35.
 */

namespace App\Entity;

use App\Validator\Constraints\CharacterLength;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use App\Entity\OntoNamespace;
use App\Entity\ProfileAssociation;
use App\Entity\OntoClass;
use App\Entity\Property;
use App\Entity\Project;
use App\Entity\SystemType;
use App\Entity\Profile;
use App\Entity\ClassAssociation;
use App\Entity\EntityAssociation;
use App\Entity\PropertyAssociation;
use App\Entity\Comment;
use App\Entity\User;

/**
 * Class TextProperty.
 */
#[ORM\Table(schema: 'che', name: 'text_property')]
#[ORM\Index(name: 'text_property_fk_namespace_for_version_idx', columns: ['fk_namespace_for_version'])]
#[ORM\Index(name: 'text_property_fk_entity_namespace_for_version_idx', columns: ['fk_entity_namespace_for_version'])]
#[ORM\Index(name: 'text_property_fk_entity_association_idx', columns: ['fk_entity_association'])]
#[ORM\Index(name: 'text_property_creator_idx', columns: ['creator'])]
#[ORM\Index(name: 'text_property_modifier_idx', columns: ['modifier'])]
#[ORM\Entity]
#[Assert\GroupSequenceProvider]
class TextProperty implements GroupSequenceProviderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_text_property')]
    private int $id;
    
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 40,
        minMessage: 'Your description must be at least {{ limit }} characters long'
    )]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $textProperty;

    #[Assert\NotBlank]
    #[ORM\Column(nullable: false)]
    private string $languageIsoCode;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $namespaceForVersion = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_associates_profile', referencedColumnName: 'pk_associates_profile', nullable: true)]
    private ?ProfileAssociation $profileAssociation = null;

    #[Assert\Valid]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class', nullable: true)]
    private ?OntoClass $class = null;

    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property', nullable: true)]
    private ?Property $property = null;

    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $namespace = null;

    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: true)]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_text_property_type', referencedColumnName: 'pk_system_type', options: ['comment' => 'Typed by a system type'])]
    private SystemType $systemType;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    private SystemType $validationStatus;

    #[Assert\Valid]
    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile')]
    private Profile $profile;

    #[Assert\Valid]
    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_is_subclass_of', referencedColumnName: 'pk_is_subclass_of')]
    private ClassAssociation $classAssociation;

    #[Assert\Valid]
    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_entity_association', referencedColumnName: 'pk_entity_association')]
    private EntityAssociation $entityAssociation;

    #[Assert\Valid]
    #[ORM\ManyToOne(inversedBy: 'textProperties')]
    #[ORM\JoinColumn(name: 'fk_is_subproperty_of', referencedColumnName: 'pk_is_subproperty_of')]
    private PropertyAssociation $propertyAssociation;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'textProperty')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_entity_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $entityNamespaceForVersion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $importerText = null;

    #[ORM\Column(nullable: true)]
    private ?int $importerInteger = null;

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

    public function getImporterText(): ?string
    {
        return $this->importerText;
    }

    public function setImporterText(?string $importerText): static
    {
        $this->importerText = $importerText;

        return $this;
    }

    public function getImporterInteger(): ?int
    {
        return $this->importerInteger;
    }

    public function setImporterInteger(?int $importerInteger): static
    {
        $this->importerInteger = $importerInteger;

        return $this;
    }
}
