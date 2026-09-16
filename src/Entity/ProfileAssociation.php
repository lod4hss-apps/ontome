<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/04/2018
 * Time: 22:07.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileAssociation.
 */
#[ORM\Table(schema: 'che', name: 'associates_profile')]
#[ORM\Entity]
class ProfileAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_associates_profile')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'profileAssociations')]
    private $profile;

    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    #[ORM\ManyToOne(targetEntity: Property::class)]
    private $property;

    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: OntoClass::class)]
    private $class;

    #[ORM\JoinColumn(name: 'fk_entity_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $entityNamespaceForVersion;

    #[ORM\JoinColumn(name: 'fk_inheriting_domain_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: OntoClass::class)]
    private $domain;

    #[ORM\JoinColumn(name: 'fk_domain_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $domainNamespace;

    #[ORM\JoinColumn(name: 'fk_inheriting_range_class', referencedColumnName: 'pk_class')]
    #[ORM\ManyToOne(targetEntity: OntoClass::class)]
    private $range;

    #[ORM\JoinColumn(name: 'fk_range_namespace', referencedColumnName: 'pk_namespace', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \OntoNamespace::class)]
    private $rangeNamespace;

    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type')]
    #[ORM\ManyToOne(targetEntity: \SystemType::class, inversedBy: 'profileAssociations')]
    private $systemType;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'profileAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private $textProperties;

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

    public function getProfile()
    {
        return $this->profile;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    /**
     * @return ArrayCollection|TextProperty[]
     */
    public function getTextProperties()
    {
        return $this->textProperties;
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

    public function getEntityNamespaceForVersion()
    {
        return $this->entityNamespaceForVersion;
    }

    public function getObjectType()
    {
        if (!is_null($this->getClass())) {
            return 'class';
        }
        if (!is_null($this->getProperty())) {
            return 'property';
        }
    }

    public function getDomainNamespace()
    {
        return $this->domainNamespace;
    }

    public function getRangeNamespace()
    {
        return $this->rangeNamespace;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setRange($range)
    {
        $this->range = $range;
    }

    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
    }

    /**
     * @param mixed $textProperty
     */
    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setProfileAssociation($this);
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

    public function setEntityNamespaceForVersion($entityNamespaceForVersion)
    {
        $this->entityNamespaceForVersion = $entityNamespaceForVersion;
    }

    public function setDomainNamespace($domainNamespace)
    {
        $this->domainNamespace = $domainNamespace;
    }

    public function setRangeNamespace($rangeNamespace)
    {
        $this->rangeNamespace = $rangeNamespace;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
