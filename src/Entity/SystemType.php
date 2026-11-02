<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 11/07/2017
 * Time: 11:51.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SystemType.
 */
#[ORM\Table(schema: 'che', name: 'system_type')]
#[ORM\Entity]
class SystemType
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_system_type')]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column(length: 250, nullable: false, options: ['comment' => 'A table name where a system type can be used. Many table names can be provided: they have to be separated by commas without spaces'])]
    private string $usedInTable;

    #[ORM\Column(length: 500, nullable: false)]
    private string $standardLabel;

    #[Assert\NotBlank]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'systemType')]
    private Collection $textProperties;

    #[Assert\NotBlank]
    #[ORM\OneToMany(targetEntity: ProfileAssociation::class, mappedBy: 'systemType')]
    private Collection $profileAssociations;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
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

    public function __construct()
    {
        $this->textProperties = new ArrayCollection();
        $this->profileAssociations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsedInTable()
    {
        return $this->usedInTable;
    }

    public function getStandardLabel()
    {
        return $this->standardLabel;
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

    /**
     * @return ArrayCollection|TextProperty[]
     */
    public function getTextProperties()
    {
        return $this->textProperties;
    }

    /**
     * @return ArrayCollection|ProfileAssociation[]
     */
    public function getProfileAssociations()
    {
        return $this->profileAssociations;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return $this->standardLabel;
    }

    public function __toString()
    {
        return (string) $this->standardLabel;
    }
}
