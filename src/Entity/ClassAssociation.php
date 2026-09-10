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
 *
 * @ORM\Entity
 *
 * @ORM\Table(schema="che", name="is_subclass_of")
 *
 * @UniqueEntity(
 *     fields={"childClass", "parentClass", "namespaceForVersion"},
 *     message="This parent class is already associated with this child class"
 * )
 */
class ClassAssociation
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer", name="pk_is_subclass_of")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="OntoClass", inversedBy="childClassAssociations")
     *
     * @ORM\JoinColumn(name="is_child_class", referencedColumnName="pk_class")
     */
    private $childClass;

    /**
     * @ORM\ManyToOne(targetEntity="OntoNamespace")
     *
     * @ORM\JoinColumn(name="fk_child_class_namespace", referencedColumnName="pk_namespace", nullable=false)
     */
    private $childClassNamespace;

    /**
     * @ORM\ManyToOne(targetEntity="OntoClass", inversedBy="parentClassAssociations")
     *
     * @ORM\JoinColumn(name="is_parent_class", referencedColumnName="pk_class")
     */
    private $parentClass;

    /**
     * @ORM\ManyToOne(targetEntity="OntoNamespace")
     *
     * @ORM\JoinColumn(name="fk_parent_class_namespace", referencedColumnName="pk_namespace", nullable=false)
     */
    private $parentClassNamespace;

    /**
     * @ORM\ManyToOne(targetEntity="OntoNamespace", inversedBy="classAssociations")
     *
     * @ORM\JoinColumn(name="fk_namespace_for_version", referencedColumnName="pk_namespace", nullable=false)
     */
    private $namespaceForVersion;

    /**
     * @ORM\ManyToOne(targetEntity="SystemType")
     *
     * @ORM\JoinColumn(name="validation_status", referencedColumnName="pk_system_type")
     *
     * @Assert\Type(type="App\Entity\SystemType")
     */
    private $validationStatus;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @Assert\NotNull()
     *
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TextProperty", mappedBy="classAssociation", cascade={"persist"})
     *
     * @ORM\OrderBy({"languageIsoCode" = "ASC"})
     */
    private $textProperties;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="classAssociation")
     *
     * @ORM\OrderBy({"creationTime" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @ORM\JoinColumn(name="creator", referencedColumnName="pk_user", nullable=false)
     */
    private $creator;

    /**
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

    /**
     * @Assert\NotBlank()
     */
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
