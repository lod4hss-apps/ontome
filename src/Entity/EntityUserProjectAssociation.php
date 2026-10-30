<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/04/2018
 * Time: 22:07.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\UserProjectAssociation;
use App\Entity\OntoNamespace;
use App\Entity\Profile;
use App\Entity\SystemType;
use App\Entity\User;
use Doctrine\ORM\Mapping\SequenceGenerator;

/**
 * Class EntityUserProjectAssociation.
 */
#[ORM\Table(schema: 'che', name: 'asso_entity_to_user_project')]
#[ORM\Index(name: 'asso_entity_to_user_project_creator_idx', columns: ['creator'])]
#[ORM\Index(name: 'asso_entity_to_user_project_modifier_idx', columns: ['modifier'])]
#[ORM\Entity]
class EntityUserProjectAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer', name: 'pk_asso_entity_to_user_project')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_associate_user_to_project', referencedColumnName: 'pk_associate_user_to_project', nullable: false)]
    #[ORM\ManyToOne(targetEntity: UserProjectAssociation::class, inversedBy: 'entityUserProjectAssociations')]
    private $userProjectAssociation;

    #[ORM\JoinColumn(name: 'fk_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    #[ORM\ManyToOne(targetEntity: OntoNamespace::class, inversedBy: 'namespaceUserProjectAssociation')]
    private $namespace;

    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'profileUserProjectAssociation')]
    private $profile;

    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type', nullable: false)]
    #[ORM\ManyToOne(targetEntity: SystemType::class)]
    private $systemType;

    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private $creator;

    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private $modifier;

    #[ORM\Column(type: 'datetime', options: ['default' => 'now'])]
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

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
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
