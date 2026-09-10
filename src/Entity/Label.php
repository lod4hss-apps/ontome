<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 29/06/2017
 * Time: 12:22.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Label.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LabelRepository")
 *
 * @ORM\Table(schema="che", name="label")
 */
class Label
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer", name="pk_label")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $label;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $inverseLabel;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $languageIsoCode;

    /**
     * @ORM\ManyToOne(targetEntity="OntoNamespace", inversedBy="labelVersions")
     *
     * @ORM\JoinColumn(name="fk_namespace_for_version", referencedColumnName="pk_namespace", nullable=false)
     */
    private $namespaceForVersion;

    /**
     * @ORM\Column(type="integer")
     */
    private $importerInteger;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isStandardLabelForLanguage;

    /**
     * @ORM\ManyToOne(targetEntity="OntoClass", inversedBy="labels")
     *
     * @ORM\JoinColumn(name="fk_class", referencedColumnName="pk_class")
     */
    private $class;

    /**
     * @ORM\ManyToOne(targetEntity="Property", inversedBy="labels")
     *
     * @ORM\JoinColumn(name="fk_property", referencedColumnName="pk_property")
     */
    private $property;

    /**
     * @ORM\ManyToOne(targetEntity="OntoNamespace", inversedBy="labels")
     *
     * @ORM\JoinColumn(name="fk_namespace", referencedColumnName="pk_namespace")
     */
    private $namespace;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="labels")
     *
     * @ORM\JoinColumn(name="fk_project", referencedColumnName="pk_project")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="labels")
     *
     * @ORM\JoinColumn(name="fk_profile", referencedColumnName="pk_profile")
     */
    private $profile;

    /**
     * @ORM\ManyToOne(targetEntity="SystemType")
     *
     * @ORM\JoinColumn(name="validation_status", referencedColumnName="pk_system_type")
     *
     * @Assert\Type(type="App\Entity\SystemType")
     */
    private $validationStatus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="label")
     *
     * @ORM\OrderBy({"creationTime" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @ORM\JoinColumn(name="creator", referencedColumnName="pk_user", nullable=false)
     */
    private $creator;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @ORM\JoinColumn(name="modifier", referencedColumnName="pk_user", nullable=false)
     */
    private $modifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modificationTime;

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
