<?php

/**
 * Created by PhpStorm.
 * User: pc-alexandre-pro
 * Date: 07/05/2019
 * Time: 14:45.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EntityAssociation.
 */
#[ORM\Table(name: 'che.associates_referenced_namespace')]
#[ORM\Entity]
class ReferencedNamespaceAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_associates_referenced_namespace')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_referenced_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $referencedNamespace;

    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Project::class)]
    private $project;

    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Profile::class)]
    private $profile;

    #[ORM\JoinColumn(name: 'fk_associate_user_to_project', referencedColumnName: 'pk_associate_user_to_project', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \UserProjectAssociation::class)]
    private $userProjectAssociation;

    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $namespace;

    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \SystemType::class)]
    private $systemType;

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

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getReferencedNamespace()
    {
        return $this->referencedNamespace;
    }

    public function setReferencedNamespace($referencedNamespace)
    {
        $this->referencedNamespace = $referencedNamespace;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function getUserProjectAssociation()
    {
        return $this->userProjectAssociation;
    }

    public function setUserProjectAssociation($userProjectAssociation)
    {
        $this->userProjectAssociation = $userProjectAssociation;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function getSystemType()
    {
        return $this->systemType;
    }

    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getModifier()
    {
        return $this->modifier;
    }

    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function getModificationTime()
    {
        return $this->modificationTime;
    }

    public function setModificationTime($modificationTime)
    {
        $this->modificationTime = $modificationTime;
    }
}
