<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 24/10/2019
 * Time: 13:37.
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Project;
use App\Entity\OntoNamespace;
use App\Entity\SystemType;
use App\Entity\Profile;
use App\Entity\User;

/**
 * Class ProjectAssociation.
 */
#[ORM\Table(schema: 'che', name: 'associates_project')]
#[ORM\Index(name: 'associates_project_fk_project_idx', columns: ['fk_project'])]
#[ORM\Index(name: 'associates_project_fk_system_type_idx', columns: ['fk_system_type'])]
#[ORM\Index(name: 'associates_project_fk_profile_idx', columns: ['fk_profile'])]
#[ORM\Entity]
class ProjectAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_associates_project')]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'projectAssociations')]
    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(inversedBy: 'projectAssociations')]
    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    private ?OntoNamespace $namespace = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type', nullable: false)]
    private SystemType $systemType;

    #[ORM\ManyToOne(inversedBy: 'projectAssociations')]
    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile', nullable: true)]
    private ?Profile $profile = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    private User $creator;

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
     * @param DateTime $creationTime
     */
    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    /**
     * @param DateTime $modificationTime
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
