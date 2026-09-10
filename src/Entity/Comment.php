<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/10/2018
 * Time: 13:40.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Comment.
 *
 * @ORM\Entity
 *
 * @ORM\Table(schema="che", name="comment")
 */
class Comment
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer", name="pk_comment")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="commentedComment")
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="commentedComment")
     *
     * @ORM\JoinColumn(name="fk_commented_comment", referencedColumnName="pk_comment")
     */
    private $commentedComment;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $viewedBy = [];

    /**
     * @ORM\ManyToOne(targetEntity="OntoNamespace", inversedBy="commentVersions")
     *
     * @ORM\JoinColumn(name="fk_namespace_for_version", referencedColumnName="pk_namespace", nullable=false)
     */
    private $namespaceForVersion;

    /**
     * @ORM\ManyToOne(targetEntity="OntoClass", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_class", referencedColumnName="pk_class")
     */
    private $class;

    /**
     * @ORM\ManyToOne(targetEntity="Property", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_property", referencedColumnName="pk_property")
     */
    private $property;

    /**
     * @ORM\ManyToOne(targetEntity="ClassAssociation", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_is_subclass_of", referencedColumnName="pk_is_subclass_of")
     */
    private $classAssociation;

    /**
     * @ORM\ManyToOne(targetEntity="PropertyAssociation", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_is_subproperty_of", referencedColumnName="pk_is_subproperty_of")
     */
    private $propertyAssociation;

    /**
     * @ORM\ManyToOne(targetEntity="EntityAssociation", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_entity_association", referencedColumnName="pk_entity_association")
     */
    private $entityAssociation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TextProperty", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_text_property", referencedColumnName="pk_text_property")
     */
    private $textProperty;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Label", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_label", referencedColumnName="pk_label")
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OntoNamespace", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="fk_namespace", referencedColumnName="pk_namespace")
     */
    private $namespace;

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

    public function getComment()
    {
        return $this->comment;
    }

    public function getAnswers()
    {
        return $this->answers;
    }

    public function getViewedBy()
    {
        return $this->viewedBy;
    }

    public function getCommentedComment()
    {
        return $this->commentedComment;
    }

    /**
     * @return OntoClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
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
     * @return PropertyAssociation
     */
    public function getEntityAssociation()
    {
        return $this->entityAssociation;
    }

    /**
     * @return TextProperty
     */
    public function getTextProperty()
    {
        return $this->textProperty;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return OntoNamespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return User
     */
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

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function setClassAssociation($classAssociation)
    {
        $this->classAssociation = $classAssociation;
    }

    public function setPropertyAssociation($propertyAssociation)
    {
        $this->propertyAssociation = $propertyAssociation;
    }

    public function setEntityAssociation($entityAssociation)
    {
        $this->entityAssociation = $entityAssociation;
    }

    public function setTextProperty($textProperty)
    {
        $this->textProperty = $textProperty;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
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

    public function setViewedBy(array $viewedBy)
    {
        $this->viewedBy = $viewedBy;
    }
}
