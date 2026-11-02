<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 04/04/2018
 * Time: 22:07.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileAssociation.
 */
#[ORM\Table(schema: 'che', name: 'associates_profile')]
#[ORM\Index(name: 'associates_profile_fk_profile_idx', columns: ['fk_profile'])]
#[ORM\Index(name: 'associates_profile_fk_property_idx', columns: ['fk_property'])]
#[ORM\Index(name: 'associates_profile_fk_class_idx', columns: ['fk_class'])]
#[ORM\Index(name: 'associates_profile_fk_entity_namespace_for_version_idx', columns: ['fk_entity_namespace_for_version'])]
#[ORM\Index(name: 'associates_profile_fk_inheriting_domain_class_idx', columns: ['fk_inheriting_domain_class'])]
#[ORM\Index(name: 'associates_profile_fk_domain_namespace_idx', columns: ['fk_domain_namespace'])]
#[ORM\Index(name: 'associates_profile_fk_inheriting_range_class_idx', columns: ['fk_inheriting_range_class'])]
#[ORM\Index(name: 'associates_profile_fk_range_namespace_idx', columns: ['fk_range_namespace'])]
#[ORM\Index(name: 'associates_profile_fk_system_type_idx', columns: ['fk_system_type'])]
#[ORM\Entity]
class ProfileAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_associates_profile')]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'profileAssociations')]
    #[ORM\JoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile', nullable: false)]
    private Profile $profile;

    #[ORM\ManyToOne(inversedBy: 'profileAssociations')]
    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    private Property $property;

    #[ORM\ManyToOne(inversedBy: 'profileAssociations')]
    #[ORM\JoinColumn(name: 'fk_class', referencedColumnName: 'pk_class')]
    private OntoClass $class;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_entity_namespace_for_version', referencedColumnName: 'pk_namespace', nullable: false)]
    private OntoNamespace $entityNamespaceForVersion;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_inheriting_domain_class', referencedColumnName: 'pk_class')]
    private OntoClass $domain;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_domain_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    private OntoNamespace $domainNamespace;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_inheriting_range_class', referencedColumnName: 'pk_class')]
    private OntoClass $range;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_range_namespace', referencedColumnName: 'pk_namespace', nullable: true)]
    private OntoNamespace $rangeNamespace;

    #[ORM\ManyToOne(inversedBy: 'profileAssociations')]
    #[ORM\JoinColumn(name: 'fk_system_type', referencedColumnName: 'pk_system_type')]
    private SystemType $systemType;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'profileAssociation', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private Collection $textProperties;

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
