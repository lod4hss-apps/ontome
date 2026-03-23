<?php
/**
 * Created by .
 * User: Alexandre
 * Date: 19/02/2026
 * Time: 10:40
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Container
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContainerRepository")
 * @ORM\Table(schema="che", name="container")
 */
class Container
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="pk_container")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="containers")
     * @ORM\JoinColumn(name="fk_project", referencedColumnName="pk_project", nullable=false)
     */
    private $project;

    /**
     * @ORM\ManyToMany(targetEntity="OntoNamespace", inversedBy="containers")
     * @ORM\JoinTable(schema="che", name="associates_container_namespace",
     *      joinColumns={@ORM\JoinColumn(name="fk_container", referencedColumnName="pk_container")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="fk_namespace", referencedColumnName="pk_namespace")}
     *      )
     */
    private $namespaces;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Label", inversedBy="containers")
     * @ORM\JoinColumn(name="fk_label", referencedColumnName="pk_label")
     */
    private $label;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOngoing;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="creator", referencedColumnName="pk_user", nullable=false)
     */
    private $creator;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="modifier", referencedColumnName="pk_user", nullable=false)
     */
    private $modifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modificationTime;

    /**
     * @return mixed
     */
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

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getIsOngoing()
    {       
         return $this->isOngoing;
    }

    /**
     * @param mixed $isOngoing
     */    
    public function setIsOngoing($isOngoing)
    {        
        $this->isOngoing = $isOngoing;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @param mixed $modifier
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * @param mixed $creationTime
     */
    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    /**
     * @param mixed $modificationTime
     */
    public function setModificationTime($modificationTime)
    {
        $this->modificationTime = $modificationTime;
    }

        /**
        * @param mixed $project
        */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
    * @param mixed $namespace
    */
    public function addNamespace($namespace)
    {
        if(!$this->namespaces->contains($namespace)){
            $this->namespaces->add($namespace);
        }
    }

    public function removeNamespace($namespace)
    {
        if($this->namespaces->contains($namespace)){
            $this->namespaces->removeElement($namespace);
        }
    }

    /**
    * @return mixed
    */
    public function getNamespaces()
    {
        return $this->namespaces;
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
     * @return mixed
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * @return mixed
     */
    public function getModificationTime()
    {
        return $this->modificationTime;
    }
}