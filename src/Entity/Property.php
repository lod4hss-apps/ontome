<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 12/07/2017
 * Time: 12:00.
 */

namespace App\Entity;

use App\Utils\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class Property.
 */
#[ORM\Table(schema: 'che', name: 'property')]
#[ORM\Entity(repositoryClass: \App\Repository\PropertyRepository::class)]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_property')]
    private $id;

    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_.]+$/', message: 'This identifier should be alphanumeric without space. Underscores and dots are allowed.')]
    #[ORM\Column(type: 'string')]
    private $identifierInNamespace;

    #[ORM\Column(type: 'string', name: 'identifier_in_uri')]
    #[Assert\NotBlank(message: 'The identifier in URI field cannot be empty')]
    private $identifierInURI;

    #[ORM\Column(type: 'text')]
    private $importerXmlField;

    #[ORM\Column(type: 'text')]
    private $importerTextField;

    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'pk_user', nullable: false)]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \User::class)]
    private $creator;

    #[ORM\JoinColumn(name: 'modifier', referencedColumnName: 'pk_user', nullable: false)]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \User::class)]
    private $modifier;

    #[ORM\Column(type: 'datetime')]
    private $creationTime;

    #[ORM\Column(type: 'datetime')]
    private $modificationTime;

    #[ORM\Column(type: 'text')]
    private $notes;

    /**
     * @var bool
     *           A non-persisted field that's used to know if the $identifierInNamespace field is manually set by the user
     *           or automatically set by a trigger in the database     *
     */
    private $isManualIdentifier;

    #[ORM\OneToMany(targetEntity: PropertyVersion::class, mappedBy: 'property', cascade: ['persist'])]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private $propertyVersions;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: TextProperty::class, mappedBy: 'property', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private $textProperties;

    #[Assert\Valid]
    #[Assert\NotNull]
    #[ORM\OneToMany(targetEntity: Label::class, mappedBy: 'property', cascade: ['persist'])]
    #[ORM\OrderBy(['languageIsoCode' => 'ASC'])]
    private $labels;

    #[ORM\JoinTable(schema: 'che', name: 'associates_profile')]
    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    #[ORM\InverseJoinColumn(name: 'fk_profile', referencedColumnName: 'pk_profile')]
    #[ORM\ManyToMany(targetEntity: \Profile::class, inversedBy: 'Property', fetch: 'EXTRA_LAZY')]
    private $profiles;

    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProfileAssociation::class, mappedBy: 'property', cascade: ['persist'])]
    #[ORM\OrderBy(['systemType' => 'ASC'])]
    private $profileAssociations;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'property')]
    #[ORM\OrderBy(['creationTime' => 'DESC'])]
    private $comments;

    #[ORM\OneToMany(targetEntity: \PropertyAssociation::class, mappedBy: 'childProperty')]
    private $childPropertyAssociations;

    #[ORM\OneToMany(targetEntity: \PropertyAssociation::class, mappedBy: 'parentProperty')]
    private $parentPropertyAssociations;

    #[ORM\OneToMany(targetEntity: \EntityAssociation::class, mappedBy: 'sourceProperty')]
    private $sourceEntityAssociations;

    #[ORM\OneToMany(targetEntity: \EntityAssociation::class, mappedBy: 'targetProperty')]
    private $targetEntityAssociations;

    #[ORM\JoinTable(schema: 'che', name: 'property_version')]
    #[ORM\JoinColumn(name: 'fk_property', referencedColumnName: 'pk_property')]
    #[ORM\InverseJoinColumn(name: 'fk_namespace_for_version', referencedColumnName: 'pk_namespace')]
    #[ORM\ManyToMany(targetEntity: \OntoNamespace::class, inversedBy: 'Property', fetch: 'EXTRA_LAZY')]
    private $namespaces;

    #[ORM\Column(type: 'boolean')]
    private $isRecursive;

    public function __construct()
    {
        $this->namespaces = new ArrayCollection();
        $this->propertyVersions = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->textProperties = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->profileAssociations = new ArrayCollection();
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // check if the identifier is set when needed
        if ($this->isManualIdentifier && empty($this->identifierInNamespace)) {
            $context->buildViolation('The identifier cannot be null.')
                ->atPath('identifierInNamespace')
                ->addViolation();
        } elseif ($this->isManualIdentifier) {
            // Retrouver l'ensemble d'espaces de noms concernés pour l'identifiant.
            // Il ne faut donc PAS utiliser $this->getNamespaces qui ne retrouve que les namespaces de CETTE classe
            // (d'autres namespaces du même root mais qui n'ont pas cette classe, peuvent donc échapper)
            // il faut donc simplement récupérer le root et boucler dessus
            $rootNamespace = $this->getPropertyVersionForDisplay()->getNamespaceForVersion()->getTopLevelNamespace();
            $uniqueIdentifiant = true;
            foreach ($rootNamespace->getChildVersions() as $namespace) {
                foreach ($namespace->getProperties() as $property) {
                    if ($property->identifierInNamespace == $this->identifierInNamespace and $property != $this) {
                        $uniqueIdentifiant = false;
                        break;
                    }
                }
                // Il faut aussi boucler sur les identifiants des classes
                foreach ($namespace->getClasses() as $class) {
                    if ($class->getIdentifierInNamespace() == $this->identifierInNamespace) {
                        $uniqueIdentifiant = false;
                        break;
                    }
                }
            }
            if (!$uniqueIdentifiant) {
                $context->buildViolation('The identifier must be unique within the same namespace. Please enter a different one.')
                    ->atPath('identifierInNamespace')
                    ->addViolation();
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIdentifierInNamespace()
    {
        return $this->identifierInNamespace;
    }

    public function getIdentifierInURI()
    {
        return $this->identifierInURI;
    }

    public function getImporterXmlField()
    {
        return $this->importerXmlField;
    }

    /**
     * @return bool
     */
    public function isManualIdentifier()
    {
        return $this->isManualIdentifier;
    }

    public function getPropertyVersions()
    {
        return $this->propertyVersions;
    }

    public function getImporterTextField()
    {
        return $this->importerTextField;
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
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
     * @return ArrayCollection|OntoNamespace[]
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @return OntoNamespace
     */
    public function getTopLevelNamespace()
    {
        /* On n'a besoin que d'un seul namespace, donc on pioche le 1er */
        return $this->getNamespaces()[0]->getTopLevelNamespace();
    }

    /**
     * @return ArrayCollection|Label[]
     */
    public function getLabels()
    {
        $labels = $this->labels->toArray();

        // Fonction de comparaison personnalisée pour trier les labels
        usort($labels, function ($a, $b) {
            $order = ['en', 'fr'];

            $aIsoCode = $a->getLanguageIsoCode();
            $bIsoCode = $b->getLanguageIsoCode();

            // Si les deux codes sont dans l'ordre personnalisé, comparez-les
            if (in_array($aIsoCode, $order) && in_array($bIsoCode, $order)) {
                return array_search($aIsoCode, $order) - array_search($bIsoCode, $order);
            }

            // Si l'un des codes est dans l'ordre personnalisé, placez-le en premier
            if (in_array($aIsoCode, $order)) {
                return -1;
            }
            if (in_array($bIsoCode, $order)) {
                return 1;
            }

            // Les deux codes ne sont pas dans l'ordre personnalisé, ne modifiez pas l'ordre
            return 0;
        });

        $acLabels = new ArrayCollection();
        foreach ($labels as $label) {
            $acLabels->add($label);
        }

        return $acLabels;
    }

    /**
     * @return ArrayCollection|TextProperty[]
     */
    public function getTextProperties()
    {
        return $this->textProperties;
    }

    /**
     * @return ArrayCollection|Profile[]
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * @return ArrayCollection|PropertyAssociation[]
     */
    public function getParentPropertyAssociations()
    {
        return $this->parentPropertyAssociations;
    }

    /**
     * @return ArrayCollection|PropertyAssociation[]
     */
    public function getChildPropertyAssociations()
    {
        return $this->childPropertyAssociations;
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
        return $this->identifierInNamespace;
    }

    public function setIdentifierInNamespace($identifierInNamespace)
    {
        $this->identifierInNamespace = $identifierInNamespace;
    }

    public function setIdentifierInURI($identifierInURI)
    {
        $this->identifierInURI = $identifierInURI;
    }

    /**
     * @param bool $isManualIdentifier
     */
    public function setIsManualIdentifier($isManualIdentifier)
    {
        $this->isManualIdentifier = $isManualIdentifier;
    }

    public function setPropertyVersions($propertyVersions)
    {
        $this->propertyVersions = $propertyVersions;
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

    public function setTextProperties($textProperties)
    {
        $this->textProperties = $textProperties;
    }

    public function setProfiles($profiles)
    {
        $this->profiles = $profiles;
    }

    public function setNamespaces($namespaces)
    {
        $this->namespaces = $namespaces;
    }

    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    public function addPropertyVersion(PropertyVersion $propertyVersion)
    {
        if ($this->propertyVersions->contains($propertyVersion)) {
            return;
        }
        $this->propertyVersions[] = $propertyVersion;
        // needed to update the owning side of the relationship!
        $propertyVersion->setProperty($this);
    }

    public function addTextProperty(TextProperty $textProperty)
    {
        if ($this->textProperties->contains($textProperty)) {
            return;
        }
        $this->textProperties[] = $textProperty;
        // needed to update the owning side of the relationship!
        $textProperty->setProperty($this);
    }

    public function addLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            return;
        }
        $this->labels[] = $label;
        // needed to update the owning side of the relationship!
        $label->setProperty($this);
    }

    public function addProfileAssociation(ProfileAssociation $profileAssociation)
    {
        if ($this->profileAssociations->contains($profileAssociation)) {
            return;
        }
        $this->profileAssociations[] = $profileAssociation;
        // needed to update the owning side of the relationship!
        $profileAssociation->setProperty($this);
    }

    public function getSourceEntityAssociations()
    {
        return $this->sourceEntityAssociations;
    }

    public function setSourceEntityAssociations($sourceEntityAssociations)
    {
        $this->sourceEntityAssociations = $sourceEntityAssociations;
    }

    public function getTargetEntityAssociations()
    {
        return $this->targetEntityAssociations;
    }

    public function setTargetEntityAssociations($targetEntityAssociations)
    {
        $this->targetEntityAssociations = $targetEntityAssociations;
    }

    /**
     * @return ArrayCollection|PropertyAssociation[]
     */
    public function getEntityAssociations()
    {
        $entityAssociations = new ArrayCollection();
        foreach ($this->sourceEntityAssociations as $entityAssociation) {
            $entityAssociations->add($entityAssociation);
        }
        foreach ($this->targetEntityAssociations as $entityAssociation) {
            $entityAssociations->add($entityAssociation);
        }

        return $entityAssociations;
    }

    /**
     * @return PropertyVersion the propertyVersion to be displayed
     */
    public function getPropertyVersionForDisplay(?OntoNamespace $namespace = null)
    {
        $pvCollection = $this->getPropertyVersions();
        if (!is_null($namespace)) {
            $pvCollection = $this->getPropertyVersions()->filter(fn (PropertyVersion $propertyVersion) => $propertyVersion->getNamespaceForVersion() === $namespace);
        } else {
            if ($pvCollection->count() > 1) {
                $pvCollection = $this->getPropertyVersions()->filter(fn (PropertyVersion $propertyVersion) => $propertyVersion->getNamespaceForVersion()->getIsOngoing());
            }
        }

        // La propriété a été trouvée
        if ($pvCollection->count() == 1) {
            return $pvCollection->first();
        }

        // Avec le filtre IsOngoing ci dessus, si un NS root n'en possède pas, cvCollection est maintenant vide, il faut donc le réinitialiser
        $pvCollection = $this->getPropertyVersions();
        // filtrer que les NS qui ont bien published_at rempli
        $pvCollection = $pvCollection->filter(fn (PropertyVersion $propertyVersion) => !is_null($propertyVersion->getNamespaceForVersion()->getPublishedAt()));
        $iterator = $pvCollection->getIterator();
        $iterator->uasort(fn ($a, $b) => ($a->getNamespaceForVersion()->getPublishedAt() > $b->getNamespaceForVersion()->getPublishedAt()) ? -1 : 1);
        $collection = new ArrayCollection(iterator_to_array($iterator));

        return $collection->first();
    }

    /**
     * @return ArrayCollection|mixed Retourne un arbre hierarchique de propriétés dans un NS donné
     */
    public function getHierarchicalTreeProperties($namespace, ?ArrayCollection $tree = null, $depth = 1)
    {
        $tree ??= new ArrayCollection();
        if ($this->getParentPropertyAssociations()->filter(fn ($v) => $v->getNamespaceForVersion() == $namespace)->isEmpty()) {
            return $tree;
        }

        $parentPropertyAssociations = $this->getParentPropertyAssociations()->filter(fn ($v) => $v->getNamespaceForVersion() == $namespace);

        // Trier d'abord CRM les autres puis par identifier
        $iterator = $parentPropertyAssociations->getIterator();
        $iterator->uasort(function ($a, $b) {
            if ($a->getChildPropertyNamespace()->getTopLevelNamespace()->getId() == 7 and $b->getChildPropertyNamespace()->getTopLevelNamespace()->getId() == 7) {
                return strnatcmp($a->getChildProperty()->getIdentifierInNamespace(), $b->getChildProperty()->getIdentifierInNamespace());
            } elseif ($a->getChildPropertyNamespace()->getTopLevelNamespace()->getId() != 7 and $b->getChildPropertyNamespace()->getTopLevelNamespace()->getId() != 7) {
                // C'est la même chose que le premier if mais plus clair à la lecture.
                return strnatcmp($a->getChildProperty()->getIdentifierInNamespace(), $b->getChildProperty()->getIdentifierInNamespace());
            } elseif ($a->getChildPropertyNamespace()->getTopLevelNamespace()->getId() == 7 and $b->getChildPropertyNamespace()->getTopLevelNamespace()->getId() != 7) {
                return false;
            } elseif ($a->getChildPropertyNamespace()->getTopLevelNamespace()->getId() != 7 and $b->getChildPropertyNamespace()->getTopLevelNamespace()->getId() == 7) {
                return true;
            }
        });
        $parentPropertyAssociations = new ArrayCollection(iterator_to_array($iterator));

        foreach ($parentPropertyAssociations as $parentPropertyAssociation) {
            $tree->add([$parentPropertyAssociation->getChildProperty(), $depth, $parentPropertyAssociation->getChildPropertyNamespace()]);
            $tree = $parentPropertyAssociation->getChildProperty()->getHierarchicalTreeProperties($namespace, $tree, $depth + 1);
        }

        return $tree;
    }

    public function getIsRecursive()
    {
        return $this->isRecursive;
    }

    public function setIsRecursive($isRecursive)
    {
        $this->isRecursive = $isRecursive;
    }

    public function updateIdentifierInUri()
    {
        $uriParameter = $this->getTopLevelNamespace()->getUriParameter();
        switch ($uriParameter) {
            case 0: // Entity identifier
                $this->setIdentifierInURI($this->getIdentifierInNamespace());
                break;
            case 1: // Entity identifier + label
                $label = $this->getLabels()->filter(fn ($v) => $v->getIsStandardLabelForLanguage())->first();
                $label = StringUtils::deleteAccents($label);
                $label = str_replace(['"', "'"], '', $label);
                $newIdentifierInUri = str_replace(' ', '_', $this->getIdentifierInNamespace().' '.$label);
                $this->setIdentifierInURI($newIdentifierInUri);
                break;
            case 2: // Camel Case
                $label = $this->getLabels()->filter(fn ($v) => $v->getIsStandardLabelForLanguage())->first();
                $label = StringUtils::deleteAccents($label);
                $label = str_replace(['"', "'"], '', $label);
                $words = preg_split('/[^a-zA-Z0-9]+/', $label);
                $camelCaseString = implode('', array_map('ucfirst', $words));
                $newIdentifierInUri = lcfirst($camelCaseString);
                $this->setIdentifierInURI($newIdentifierInUri);
                break;
        }
    }
}
