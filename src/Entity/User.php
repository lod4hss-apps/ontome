<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 18/04/2017
 * Time: 17:12.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @ORM\Table(schema="che", name="admin_user")
 *
 * @UniqueEntity(fields={"email"}, message="This e-mail is already in use.")
 * @UniqueEntity(fields={"login"}, message="This login is already in use.")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer",  name="pk_user")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $login;

    /**
     * @Assert\NotBlank()
     *
     * @Assert\Email()
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $email;

    /**
     * The encoded password.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * A non-persisted field that's used to create the encoded password.
     *
     * @Assert\NotBlank(groups={"Registration","ResetPassword"})
     *
     * @Assert\Length(
     *      min = 12,
     *      minMessage = "Your password must be at least {{ limit }} characters long",
     * )
     *
     * @Assert\Regex(
     *     pattern="/^(?:(?!.*[<>])(?:(?=.*[~!\[@#$%^&*()_,|`\]\{+=?.\}\\\/<>-])(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]))).*$/",
     *     match=true,
     *     message="Your password must contain at least 1 special character, 1 digit, 1 lowercase and 1 uppercase letter"
     * )
     *
     * @var string
     */
    private $plainPassword;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $lastName;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $firstName;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $status;

    /**
     * @Assert\NotEqualTo(false, message="You must accept our term of service")
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $hasValidatedPolicy;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $institution;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $tokenDate;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\OneToMany(targetEntity="UserProjectAssociation", mappedBy="user")
     */
    private $userProjectAssociations;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="UserProjectAssociation")
     *
     * @ORM\JoinColumn(name="fk_current_active_project", referencedColumnName="pk_project", nullable=false)
     */
    private $currentActiveProject;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="creator")
     */
    private $comments;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->userProjectAssociations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getUsername(): string
    {
        return $this->login;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // give everyone ROLE_USER!
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getTokenDate()
    {
        return $this->tokenDate;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getInstitution()
    {
        return $this->institution;
    }

    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;
    }

    public function getFullName()
    {
        return trim($this->getFirstName().' '.$this->getLastName());
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getHasValidatedPolicy()
    {
        return $this->hasValidatedPolicy;
    }

    public function setHasValidatedPolicy($hasValidatedPolicy)
    {
        $this->hasValidatedPolicy = $hasValidatedPolicy;
    }

    /**
     * @return ArrayCollection|UserProjectAssociation
     */
    public function getUserProjectAssociations()
    {
        return $this->userProjectAssociations;
    }

    /**
     * @return string a human readable identification of the object
     */
    public function getObjectIdentification()
    {
        return $this->login;
    }

    /**
     * @return Project
     */
    public function getCurrentActiveProject()
    {
        return $this->currentActiveProject;
    }

    /**
     * @return OntoNamespace|null the ongoing namespace managed by the current active project
     */
    public function getCurrentOngoingNamespace()
    {
        $namespaces = $this->getCurrentActiveProject()->getManagedNamespaces()->filter(fn (OntoNamespace $namespace) => $namespace->getIsOngoing());

        if ($namespaces->first()) {
            return $namespaces->first();
        }

        return null;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setTokenDate($tokenDate)
    {
        $this->tokenDate = $tokenDate;
    }

    public function setCurrentActiveProject($currentActiveProject)
    {
        $this->currentActiveProject = $currentActiveProject;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function __toString()
    {
        $s = $this->getFullName();
        $s ??= 'Anonymous';

        return (string) $s;
    }
}
