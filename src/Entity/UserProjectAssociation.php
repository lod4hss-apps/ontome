<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/04/2018
 * Time: 22:07.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserProjectAssociation.
 */
#[ORM\Table(schema: 'che', name: 'associate_user_to_project')]
#[ORM\Entity]
#[UniqueEntity(fields: ['user', 'project'], message: 'This user class is already associated with this project')]
class UserProjectAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_associate_user_to_project')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_user', referencedColumnName: 'pk_user', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \User::class, inversedBy: 'userProjectAssociations')]
    private $user;

    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Project::class)]
    private $project;

    #[ORM\Column(type: 'integer', name: 'has_permissions_in_project')]
    #[Assert\Choice([1, 2, 3])]
    private $permission;

    #[ORM\Column(type: 'date', nullable: false)]
    private $startDate;

    #[ORM\Column(type: 'date')]
    private $endDate;

    #[ORM\OneToMany(targetEntity: \EntityUserProjectAssociation::class, mappedBy: 'userProjectAssociation')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $entityUserProjectAssociations;

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

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @return Date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getNotes()
    {
        return $this->notes;
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

    /**
     * @return \DateTime
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * @return \DateTime
     */
    public function getModificationTime()
    {
        return $this->modificationTime;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return 'User/project association n°'.$this->id;
    }

    public function getEntityUserProjectAssociations()
    {
        return $this->entityUserProjectAssociations;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @param int $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
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

    public function setEntityUserProjectAssociations($entityUserProjectAssociations)
    {
        $this->entityUserProjectAssociations = $entityUserProjectAssociations;
    }
}
