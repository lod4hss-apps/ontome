<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 24/10/2019
 * Time: 13:37.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProjectAssociation.
 */
#[ORM\Table(schema: 'che', name: 'associates_project')]
#[ORM\Entity]
class ProjectAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_associates_project')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'projectAssociations')]
    private $project;

    #[ORM\JoinColumn(name: 'fk_label', referencedColumnName: 'pk_label', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Label::class)]
    private $label;

    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    #[ORM\ManyToOne(targetEntity: OntoNamespace::class, inversedBy: 'projectAssociations')]
    private $namespace;

    #[ORM\JoinColumn(name: 'fk_text_property', referencedColumnName: 'pk_text_property', nullable: true)]
    #[ORM\ManyToOne(targetEntity: TextProperty::class)]
    private $textProperty;

    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \SystemType::class, inversedBy: 'projectAssociations')]
    private $systemType;

    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Profile::class)]
    private $profile;

    #[ORM\Column(type: 'text')]
    private $notes;

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

    public function getId()
    {
        return $this->id;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return $this->systemType;
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

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @param User $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @param User $modifier
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * @param \DateTime $creationTime
     */
    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    /**
     * @param \DateTime $modificationTime
     */
    public function setModificationTime($modificationTime)
    {
        $this->modificationTime = $modificationTime;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
