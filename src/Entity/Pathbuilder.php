<?php

/**
 * Created by .
 * User: Alexandre
 * Date: 19/02/2026
 * Time: 10:40.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Pathbuilder.
 */
#[ORM\Table(schema: 'che', name: 'pathbuilder')]
#[ORM\Entity]
class Pathbuilder
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'pk_pathbuilder')]
    private $id;

    #[ORM\JoinColumn(name: 'fk_container', referencedColumnName: 'pk_container', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Container::class, inversedBy: 'pathbuilders')]
    private $container;

    #[ORM\JoinColumn(name: 'fk_label', referencedColumnName: 'pk_label')]
    #[ORM\ManyToOne(targetEntity: \Label::class, inversedBy: 'pathbuilders')]
    private $label;

    #[ORM\Column(type: 'text', name: 'xml_content')]
    private $xmlContent;

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

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getXmlContent()
    {
        return $this->xmlContent;
    }

    public function setXmlContent($xmlContent)
    {
        $this->xmlContent = $xmlContent;
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

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
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

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function getModificationTime()
    {
        return $this->modificationTime;
    }
}
