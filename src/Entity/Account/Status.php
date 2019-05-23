<?php

namespace App\Entity\Account;

use App\Entity\PartnershipStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="accounts_statuses")
 * @ORM\Entity(repositoryClass="App\Repository\Account\StatusRepository")
 */
class Status
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $expiredAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isExpired;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PartnershipStatus")
     * @ORM\JoinColumn(name="status_uuid", referencedColumnName="uuid")
     * @Assert\Type(type="App\Entity\PartnershipStatus")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account", inversedBy="statuses")
     * @ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid")
     */
    private $account;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isExpired' => false,
            'createdAt' => new \DateTime(),
            'expiredAt' => (new \DateTime())->modify('+1 year'),
        ];

        foreach ($defaults as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    /**
     * Status clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return Account|null
     */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return Status
     */
    public function setAccount(Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return PartnershipStatus|null
     */
    public function getStatus(): ?PartnershipStatus
    {
        return $this->status;
    }

    /**
     * @param PartnershipStatus $status
     *
     * @return Status
     */
    public function setStatus(PartnershipStatus $status): self
    {
        $this->status = $status;

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
     * @return Status
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
     * @return Status
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
     * @return Status
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
        return $this->isExpired === true || ($this->expiredAt && $this->expiredAt < new \DateTime());
    }
}
