<?php

namespace App\Entity\Account;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="accounts_codes")
 * @ORM\Entity(repositoryClass="App\Repository\Account\CodeRepository")
 * @ORM\EntityListeners({"App\Listener\AccountCodeListener"})
 */
class Code
{
    /**
     * @var string
     */
    public $nonEncodedAccountCode;

    /**
     * @var bool
     */
    public $isNotificationSent = false;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=60, options={"fixed" = true})
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredAt;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isExpired;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account", inversedBy="accountCodes", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid", nullable=false)
     */
    private $account;

    /**
     * Type constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isExpired' => false,
            'createdAt' => new \DateTime(),
            'expiredAt' => (new \DateTime())->modify('+1 year'),
            'nonEncodedAccountCode' => bin2hex(random_bytes(4))
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Code
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     *
     * @return Code
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTimeInterface|null $expiredAt
     *
     * @return Code
     */
    public function setExpiredAt(?\DateTimeInterface $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsExpired(): ?bool
    {
        return $this->isExpired;
    }

    /**
     * @param bool $isExpired
     *
     * @return Code
     */
    public function setIsExpired(bool $isExpired): self
    {
        $this->isExpired = $isExpired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->isExpired === true;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return Code
     */
    public function setAccount(Account $account): self
    {
        $this->account = $account;

        return $this;
    }
}
