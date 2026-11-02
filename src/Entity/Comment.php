<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/10/2018
 * Time: 13:40.
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;

/**
 * Class Comment.
 */
#[ORM\Table(schema: 'che', name: 'comment')]
#[ORM\Index(name: 'comment_fk_namespace_for_version_idx', columns: ['fk_namespace_for_version'])]
#[ORM\Entity]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_comment')]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $comment;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'commentedComment')]
    private Collection $answers;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'fk_commented_comment', referencedColumnName: 'pk_comment')]
    private Comment $commentedComment;

    #[ORM\Column(type: 'json')]
    private array $viewedBy = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $namespaceForVersion = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class')]
    private OntoClass $class;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    private Property $property;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_is_subclass_of', referencedColumnName: 'pk_is_subclass_of')]
    private ClassAssociation $classAssociation;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_is_subproperty_of', referencedColumnName: 'pk_is_subproperty_of')]
    private PropertyAssociation $propertyAssociation;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_entity_association', referencedColumnName: 'pk_entity_association')]
    private EntityAssociation $entityAssociation;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_text_property', referencedColumnName: 'pk_text_property')]
    private TextProperty $textProperty;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_label', referencedColumnName: 'pk_label')]
    private Label $label;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace')]
    private OntoNamespace $namespace;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(inversedBy: 'comments')]
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
     * @return EntityAssociation
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
