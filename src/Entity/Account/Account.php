<?php

namespace App\Entity\Account;

use App\Entity\Address;
use App\Entity\Site;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="accounts")
 * @ORM\Entity(repositoryClass="App\Repository\Account\AccountRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 * @UniqueEntity("email")
 */
class Account
{
    use WorkflowTrait;

    /**
     * @var bool
     */
    public $updateAccountCode = false;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max="1000")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     * @Assert\Type("boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @Assert\Url()
     */
    private $web;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     * @Assert\Regex(pattern="/^\+\d+\s\(\d+\)\s\d+$/")
     */
    private $phone;

    /**
     * @ORM\Column(type="integer", options={"default":100})
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="accounts_addresses",
     *     joinColumns={@ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="address_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     * @var ArrayCollection
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Account\Contact", mappedBy="account", cascade={"persist", "remove"})
     * @var ArrayCollection
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Account\Status", mappedBy="account", cascade={"persist", "remove"})
     * @var ArrayCollection
     */
    private $statuses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Account\Manager", mappedBy="account", cascade={"persist", "remove"})
     * @var ArrayCollection
     */
    private $managers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Account\Discount", mappedBy="account", cascade={"persist", "remove"})
     * @var ArrayCollection
     */
    private $discounts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Site")
     * @ORM\JoinTable(name="accounts_sites",
     *     joinColumns={@ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="site_uuid", referencedColumnName="uuid")}
     * )
     * @Assert\Count(min="1", minMessage="Выберите хотя бы один сайт.")
     */
    private $sites;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Account\Type")
     * @ORM\JoinTable(name="accounts_accounts_types",
     *     joinColumns={@ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="type_uuid", referencedColumnName="uuid")}
     * )
     * @Assert\Count(min="1", minMessage="Выберите хотя бы один тип.")
     */
    private $types;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Account\Code", mappedBy="account", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt": "DESC"})
     * @var Collection
     */
    private $accountCodes;

    /**
     * Account constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'sort' => 100,
            'addresses' => new ArrayCollection(),
            'contacts' => new ArrayCollection(),
            'statuses' => new ArrayCollection(),
            'managers' => new ArrayCollection(),
            'discounts' => new ArrayCollection(),
            'sites' => new ArrayCollection(),
            'types' => new ArrayCollection(),
            'accountCodes' => new ArrayCollection(),
            'updateAccountCode' => true
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Account clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->setWorkflow(null);
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Account
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return Account
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Account
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     *
     * @return Account
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getWeb(): ?string
    {
        return $this->web;
    }

    /**
     * @param null|string $web
     *
     * @return Account
     */
    public function setWeb(?string $web): self
    {
        $this->web = $web;

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
     * @return Account
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return Account
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * @param Address $address
     *
     * @return Account
     */
    public function addAddress(Address $address): self
    {
//        $address->setAccount($this);

        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
        }

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     *
     * @return Account
     */
    public function addContact(Contact $contact): self
    {
        $contact->setAccount($this);

        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    /**
     * @return Collection|Status[]
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    /**
     * @param Status $status
     *
     * @return Account
     */
    public function addStatus(Status $status): self
    {
        $status->setAccount($this);

        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
        }

        return $this;
    }

    /**
     * @return Collection|Manager[]
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    /**
     * @param Manager $manager
     *
     * @return Account
     */
    public function addManager(Manager $manager): self
    {
        $manager->setAccount($this);

        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }

        return $this;
    }

    /**
     * @return Collection|Discount[]
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    /**
     * @param Discount $discount
     *
     * @return Account
     */
    public function addDiscount(Discount $discount): self
    {
        $discount->setAccount($this);

        if (!$this->discounts->contains($discount)) {
            $this->discounts->add($discount);
        }

        return $this;
    }

    /**
     * @return Collection|Site[]
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    /**
     * @param Site[] $sites
     *
     * @return Account
     */
    public function setSites(array $sites): self
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @return Collection|Type[]
     */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    /**
     * @param Type[] $types
     *
     * @return Account
     */
    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return ArrayCollection|Code[]
     */
    public function getAccountCodes(): ?Collection
    {
        return $this->accountCodes;
    }

    /**
     * @param Code $code
     *
     * @return Account
     */
    public function addAccountCode(Code $code): self
    {
        $code->setAccount($this);

        if (!$this->accountCodes->contains($code)) {
            $this->accountCodes->add($code);
        }

        return $this;
    }

    /**
     * @return Code|null
     */
    public function getAccountCode(): ?Code
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isExpired', false))
            ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()));

        $accountCode = $this->getAccountCodes()->matching($criteria)->first();

        return $accountCode ?: null;
    }
}
