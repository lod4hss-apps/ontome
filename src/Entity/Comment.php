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
 */
#[ORM\Table(schema: 'che', name: 'comment')]
#[ORM\Entity]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_comment')]
    private $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private $comment;

    #[ORM\OneToMany(targetEntity: \Comment::class, mappedBy: 'commentedComment')]
    private $answers;

    #[ORM\JoinColumn(name: 'fk_commented_comment', referencedColumnName: 'pk_comment')]
    #[ORM\ManyToOne(targetEntity: \Comment::class, inversedBy: 'commentedComment')]
    private $commentedComment;

    #[ORM\Column(type: 'json', nullable: true)]
    private $viewedBy = [];

    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class, inversedBy: 'commentVersions')]
    private $namespaceForVersion;

    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: \OntoClass::class, inversedBy: 'comments')]
    private $class;

    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    #[ORM\ManyToOne(targetEntity: \Property::class, inversedBy: 'comments')]
    private $property;

    #[ORM\JoinColumn(name: 'fk_is_subclass_of', referencedColumnName: 'pk_is_subclass_of')]
    #[ORM\ManyToOne(targetEntity: \ClassAssociation::class, inversedBy: 'comments')]
    private $classAssociation;

    #[ORM\JoinColumn(name: 'fk_is_subproperty_of', referencedColumnName: 'pk_is_subproperty_of')]
    #[ORM\ManyToOne(targetEntity: \PropertyAssociation::class, inversedBy: 'comments')]
    private $propertyAssociation;

    #[ORM\JoinColumn(name: 'fk_entity_association', referencedColumnName: 'pk_entity_association')]
    #[ORM\ManyToOne(targetEntity: \EntityAssociation::class, inversedBy: 'comments')]
    private $entityAssociation;

    #[ORM\JoinColumn(name: 'fk_text_property', referencedColumnName: 'pk_text_property')]
    #[ORM\ManyToOne(targetEntity: TextProperty::class, inversedBy: 'comments')]
    private $textProperty;

    #[ORM\JoinColumn(name: 'fk_label', referencedColumnName: 'pk_label')]
    #[ORM\ManyToOne(targetEntity: Label::class, inversedBy: 'comments')]
    private $label;

    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace')]
    #[ORM\ManyToOne(targetEntity: OntoNamespace::class, inversedBy: 'comments')]
    private $namespace;

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
