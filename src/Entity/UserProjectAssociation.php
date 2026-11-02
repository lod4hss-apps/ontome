<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/04/2018
 * Time: 22:07.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Entity\Project;

/**
 * Class UserProjectAssociation.
 */
#[ORM\Table(schema: 'che', name: 'associate_user_to_project')]
#[ORM\Entity]
#[UniqueEntity(fields: ['user', 'project'], message: 'This user class is already associated with this project')]
#[ORM\UniqueConstraint(name: 'uniq_user_user_project', columns: ['fk_user', 'fk_project'])]
class UserProjectAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_associate_user_to_project')]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'userProjectAssociations')]
    #[ORM\JoinColumn(name: 'fk_user', referencedColumnName: 'pk_user', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'userProjectAssociations')]
    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    private Project $project;

    #[Assert\Choice([1, 2, 3])]
    #[ORM\Column(name: 'has_permissions_in_project', nullable: false)]
    private int $permission;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $endDate = null;

    #[ORM\OneToMany(targetEntity: EntityUserProjectAssociation::class, mappedBy: 'userProjectAssociation')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $entityUserProjectAssociations;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    private User $creator;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    private User $modifier;

    #[ORM\Column(nullable: false, options: ['default' => 'now'])]
    private DateTime $creationTime;

    #[ORM\Column(nullable: true)]
    private ?DateTime $modificationTime = null;

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
     * @return DateTime
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
