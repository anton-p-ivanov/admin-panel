<?php

namespace App\Entity\User;

use App\Entity\Role;
use App\Entity\Site;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users", indexes={@Index(name="IDX_FULLNAME", columns={"full_name"})})
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 * @UniqueEntity("email")
 */
class User implements UserInterface, EquatableInterface, \Serializable
{
    use WorkflowTrait;

    /**
     * Scenarios constants
     */
    const
        SCENARIO_USER_REGISTER = 1,
        SCENARIO_USER_UPDATE = 2,
        SCENARIO_USER_RESET = 3;

    /**
     * @var int
     */
    public $scenario;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\Length(max="100")
     * @Assert\NotBlank()
     */
    private $fname;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\Length(max="100")
     * @Assert\NotBlank()
     */
    private $lname;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(max="100")
     */
    private $sname;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $fullName;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $isConfirmed;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     * @Assert\Regex(pattern="/^\+\d+\s\(\d+\)\s\d+$/")
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     * @Assert\Regex(pattern="/^\+\d+\s\(\d+\)\s\d+$/")
     */
    private $phone_mobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $skype;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type("\DateTime")
     * @Assert\LessThanOrEqual("today")
     */
    private $birthdate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Password", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private $userPasswords;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Checkword", mappedBy="user", cascade={"persist", "remove"})
     */
    private $userCheckwords;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role")
     * @ORM\JoinTable(
     *     name="users_roles",
     *     joinColumns={@ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     * @var ArrayCollection
     */
    private $userRoles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Site")
     * @ORM\JoinTable(
     *     name="users_sites",
     *     joinColumns={@ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="site_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     * @var ArrayCollection
     */
    private $userSites;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Account", mappedBy="user", cascade={"persist", "remove"})
     * @var ArrayCollection
     */
    private $accounts;

    /**
     * @var string
     * @Assert\Length(min="6")
     */
    private $password;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->scenario = self::SCENARIO_USER_REGISTER;

        $defaults = [
            'isActive' => true,
            'isConfirmed' => false,
            'userCheckwords' => new ArrayCollection(),
            'userPasswords' => new ArrayCollection(),
            'userRoles' => new ArrayCollection(),
            'userSites' => new ArrayCollection(),
            'accounts' => new ArrayCollection(),
        ];

        foreach ($defaults as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFname(): ?string
    {
        return $this->fname;
    }

    /**
     * @param string $fname
     *
     * @return User
     */
    public function setFname(string $fname): self
    {
        $this->fname = $fname;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLname(): ?string
    {
        return $this->lname;
    }

    /**
     * @param string $lname
     *
     * @return User
     */
    public function setLname(string $lname): self
    {
        $this->lname = $lname;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSname(): ?string
    {
        return $this->sname;
    }

    /**
     * @param null|string $sname
     *
     * @return User
     */
    public function setSname(?string $sname): self
    {
        $this->sname = $sname;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param null|bool $isActive
     *
     * @return User
     */
    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    /**
     * @param null|bool $isConfirmed
     *
     * @return User
     */
    public function setIsConfirmed(?bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     *
     * @return User
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoneMobile(): ?string
    {
        return $this->phone_mobile;
    }

    /**
     * @param null|string $phone_mobile
     *
     * @return User
     */
    public function setPhoneMobile(?string $phone_mobile): self
    {
        $this->phone_mobile = $phone_mobile;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSkype(): ?string
    {
        return $this->skype;
    }

    /**
     * @param null|string $skype
     *
     * @return User
     */
    public function setSkype(?string $skype): self
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    /**
     * @param \DateTime|null $birthdate
     *
     * @return User
     */
    public function setBirthdate(?\DateTime $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * @return ArrayCollection|Password[]
     */
    public function getUserPasswords(): ?Collection
    {
        return $this->userPasswords;
    }

    /**
     * @return ArrayCollection|Checkword[]
     */
    public function getUserCheckwords(): Collection
    {
        return $this->userCheckwords;
    }

    /**
     * Returns first non-expired user checkword.
     * @return Checkword|null
     */
    public function getUserCheckword(): ?Checkword
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isExpired', false))
            ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()));

        $userCheckword = $this->getUserCheckwords()->matching($criteria)->first();

        return $userCheckword ?: null;
    }

    /**
     * @return Password|null
     */
    public function getUserPassword(): ?Password
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isExpired', false))
            ->andWhere(Criteria::expr()->isNull('expiredAt'))
            ->orWhere(Criteria::expr()->gt('expiredAt', new \DateTime()));

        $userPassword = $this->getUserPasswords()->matching($criteria)->first();

        return $userPassword ?: null;
    }

    /**
     * Returns first non-expired user password.
     * @return string|null
     */
    public function getPassword(): ?string
    {
        if ($this->password === null && $userPassword = $this->getUserPassword()) {
            $this->password = $userPassword->getPassword();
        }

        return $this->password;
    }

    /**
     * @param string|null $password
     *
     * @return User
     */
    public function setPassword(?string $password): self
    {
        if ($password) {
            $userPassword = new Password();
            $userPassword->setPassword($password);
            $userPassword->setUser($this);

            $this->getUserPasswords()->add($userPassword);
        }

        return $this;
    }

    /**
     * @return User
     */
    public function setCheckword(): self
    {
        $userCheckword = new Checkword();
        $userCheckword->setUser($this);

        $this->getUserCheckwords()->add($userCheckword);

        return $this;
    }

    /**
     * @param null|string $username
     *
     * @return User
     */
    public function setUsername(?string $username): self
    {
        $this->email = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->userRoles->map(function (Role $role) {
            return $role->getCode();
        })->toArray();
    }

    /**
     * @return Collection|Role[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    /**
     * @param Role[] $roles
     *
     * @return User
     */
    public function setUserRoles(array $roles): self
    {
        $this->userRoles = $roles;

        return $this;
    }

    /**
     * @return Collection|Site[]
     */
    public function getUserSites(): Collection
    {
        return $this->userSites;
    }

    /**
     * @param array $sites
     *
     * @return User
     */
    public function setUserSites(array $sites): self
    {
        $this->userSites = $sites;

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    /**
     * @param Account $account
     *
     * @return User
     */
    public function addAccount(Account $account): self
    {
        $account->setUser($this);

        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
        }

        return $this;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->uuid,
            $this->email,
            $this->getPassword(),
            $this->isActive
        ]);
    }

    /**
     * @param $serialized
     *
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->uuid,
            $this->email,
            $this->password,
            $this->isActive
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return null|string
     */
    public function getSalt()
    {
        if ($userPassword = $this->getUserPassword()) {
            return $userPassword->getSalt();
        }

        return null;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        $password = $this->getUserPassword();

        if ($password) {
            $expiredDate = $password->getExpiredAt();
            $isExpired = (new \DateTime() > $expiredDate);

            if ($password->getIsExpired() || ($expiredDate && $isExpired)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        /* @todo Implementation needed */
    }

    /**
     * This method is used in selector form type.
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->getFullName();
    }

    /**
     * @return null|string
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     *
     * @return User
     */
    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }
}
