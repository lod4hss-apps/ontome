<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 29/06/2017
 * Time: 12:22.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Label.
 */
#[ORM\Table(schema: 'che', name: 'label')]
#[ORM\Index(name: 'label_language_iso_code_idx', columns: ['language_iso_code'])]
#[ORM\Index(name: 'label_fk_namespace_for_version_idx', columns: ['fk_namespace_for_version'])]
#[ORM\Index(name: 'label_fk_namespace_idx', columns: ['fk_namespace'])]
#[ORM\Entity]
class Label
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_label')]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $label;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $inverseLabel;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', nullable: false)]
    private string $languageIsoCode;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $namespaceForVersion = null;

    #[ORM\Column(nullable: true)]
    private int $importerInteger;

    #[ORM\Column(nullable: false)]
    private bool $isStandardLabelForLanguage;

    #[ORM\ManyToOne(inversedBy: 'labels')]
    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class')]
    private OntoClass $class;

    #[ORM\ManyToOne(inversedBy: 'labels')]
    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    private Property $property;

    #[ORM\ManyToOne(inversedBy: 'labels')]
    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    private OntoNamespace $namespace;

    #[ORM\ManyToOne(inversedBy: 'labels')]
    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project')]
    private Project $project;

    #[ORM\ManyToOne(inversedBy: 'labels')]
    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile')]
    private Profile $profile;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'validation_status', referencedColumnName: 'pk_system_type')]
    private SystemType $validationStatus;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'label')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne()]
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

    public function getLabel()
    {
        return $this->label;
    }

    public function getInverseLabel()
    {
        return $this->inverseLabel;
    }

    public function getLanguageIsoCode()
    {
        return $this->languageIsoCode;
    }

    public function getImporterInteger()
    {
        return $this->importerInteger;
    }

    public function getIsStandardLabelForLanguage()
    {
        return $this->isStandardLabelForLanguage;
    }

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
     * @return OntoClass|Property|OntoNamespace|Project|Profile|null the object described by the text property
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
        } elseif (!is_null($this->project)) {
            $object = $this->project;
        } elseif (!is_null($this->profile)) {
            $object = $this->profile;
        }

        return $object;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return $this->label;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
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

    public function setNamespaceForVersion($namespaceForVersion)
    {
        $this->namespaceForVersion = $namespaceForVersion;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setInverseLabel($inverseLabel)
    {
        $this->inverseLabel = $inverseLabel;
    }

    public function setLanguageIsoCode($languageIsoCode)
    {
        $this->languageIsoCode = $languageIsoCode;
    }

    public function setIsStandardLabelForLanguage($isStandardLabelForLanguage)
    {
        $this->isStandardLabelForLanguage = $isStandardLabelForLanguage;
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

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
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

    public function __toString()
    {
        return (string) $this->label;
    }
}
