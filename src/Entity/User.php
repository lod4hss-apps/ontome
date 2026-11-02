<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 18/04/2017
 * Time: 17:12.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(schema: 'che', name: 'admin_user')]
#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'This e-mail is already in use.')]
#[UniqueEntity(fields: ['login'], message: 'This login is already in use.')]
#[ORM\UniqueConstraint(name: 'uniq_user_login', columns: ['login'])]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pk_user')]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    private string $login;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(unique: true)]
    private string $email;

    // The encoded password.
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    // A non-persisted field that's used to create the encoded password.
    #[Assert\NotBlank(groups: ['Registration', 'ResetPassword'])]
    #[Assert\Length(min: 12, minMessage: 'Your password must be at least {{ limit }} characters long')]
    #[Assert\Regex(pattern: '/^(?:(?!.*[<>])(?:(?=.*[~!\[@#$%^&*()_,|`\]\{+=?.\}\\\\\\/<>-])(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]))).*$/', match: true, message: 'Your password must contain at least 1 special character, 1 digit, 1 lowercase and 1 uppercase letter')]
    private string $plainPassword;

    #[Assert\NotBlank]
    #[ORM\Column(nullable: false)]
    private string $lastName;

    #[Assert\NotBlank]
    #[ORM\Column(nullable: false)]
    private string $firstName;

    #[ORM\Column(nullable: false, options: ['default' => true, 'comment' => 'Définit le statut de l\'utilisateur : 1 pour actif et 0 pour inactif'])]
    private bool $status;

    #[Assert\NotEqualTo(false, message: 'You must accept our term of service')]
    #[ORM\Column(nullable: false, options: ['default' => true])]
    private bool $hasValidatedPolicy;

    #[ORM\Column(nullable: true)]
    private ?string $institution = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $tokenDate = null;

    #[Assert\NotBlank]
    #[ORM\OneToMany(targetEntity: UserProjectAssociation::class, mappedBy: 'user')]
    private Collection $userProjectAssociations;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'fk_current_active_project', referencedColumnName: 'pk_project', nullable: false)]
    private Project $currentActiveProject;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'creator')]
    private Collection $comments;

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
