<?php

namespace App\Entity\Price;

use App\Entity\Account\Account;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="prices_discounts")
 * @ORM\Entity(repositoryClass="App\Repository\Price\DiscountRepository")
 */
class Discount
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Price\Price", inversedBy="discounts")
     * @ORM\JoinColumn(name="price_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid", nullable=true, onDelete="CASCADE")
     */
    private $account;

    /**
     * @ORM\Column(type="float", precision=5, scale=4)
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1", minMessage="Значение должно быть от 0 до 100.", maxMessage="Значение должно быть от 0 до 100.")
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $expiredAt;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isExpired;

    /**
     * Discount constructor.
     */
    public function __construct()
    {
        $properties = [
            'createdAt' => new \DateTime(),
            'isExpired' => false,
        ];

        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Discount clone.
     */
    public function __clone()
    {
        $this->uuid = null;
        $this->createdAt = new \DateTime();
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return float|null
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return Discount
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Price|null
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    /**
     * @param Price $price
     *
     * @return Discount
     */
    public function setPrice(Price $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Account|null
     */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    /**
     * @param Account|null $account
     *
     * @return Discount
     */
    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Discount
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiredAt(): ?\DateTime
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime|null $expiredAt
     *
     * @return Discount
     */
    public function setExpiredAt(?\DateTime $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExpired(): bool
    {
        return $this->isExpired;
    }

    /**
     * @param bool $isExpired
     *
     * @return Discount
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
        return $this->isExpired || ($this->expiredAt && $this->expiredAt < new \DateTime());
    }
}
