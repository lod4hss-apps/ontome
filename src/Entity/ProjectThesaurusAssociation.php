<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 07/11/2022
 * Time: 14:50.
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProjectThesaurusAssociation.
 */
#[ORM\Table(schema: 'che', name: 'asso_project_thesaurus')]
#[ORM\Entity]
class ProjectThesaurusAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer', name: 'pk_asso_project_thesaurus')]
    private $id;

    #[ORM\ManyToOne(inversedBy: 'projectThesaurusAssociations')]
    #[ORM\JoinColumn(name: 'fk_project', referencedColumnName: 'pk_project', nullable: false)]
    private Project $project;

    #[Assert\Url(message: 'Please enter a valid URI')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $thesaurusURL;

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

    public function getThesaurusURL()
    {
        return $this->thesaurusURL;
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

    public function setThesaurusURL($thesaurusURL)
    {
        $this->thesaurusURL = $thesaurusURL;
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

    public function __toString()
    {
        return (string) $this->id;
    }
}
