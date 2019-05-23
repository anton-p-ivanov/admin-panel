<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="partnership_discounts")
 * @ORM\Entity(repositoryClass="App\Repository\PartnershipDiscountRepository")
 */
class PartnershipDiscount
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PartnershipStatus", inversedBy="discounts")
     * @ORM\JoinColumn(name="status_uuid", referencedColumnName="uuid")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SalesDiscount")
     * @ORM\JoinColumn(name="discount_uuid", referencedColumnName="uuid")
     */
    private $discount;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=4)
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
     * PartnershipDiscount constructor.
     */
    public function __construct()
    {
        $defaults = [
            'value' => 0,
            'createdAt' => new \DateTime(),
            'expiredAt' => (new \DateTime())->modify('+1 year'),
            'isExpired' => false,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * PartnershipDiscount clone.
     */
    public function __clone()
    {
        $this->uuid = null;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
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
     * @return PartnershipDiscount
     */
    public function setStatus(PartnershipStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return SalesDiscount
     */
    public function getDiscount(): ?SalesDiscount
    {
        return $this->discount;
    }

    /**
     * @param SalesDiscount $discount
     *
     * @return PartnershipDiscount
     */
    public function setDiscount(SalesDiscount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return PartnershipDiscount
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

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
     * @return PartnershipDiscount
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt(): \DateTime
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return PartnershipDiscount
     */
    public function setExpiredAt(\DateTime $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getisExpired(): bool
    {
        return $this->isExpired;
    }

    /**
     * @param bool $isExpired
     *
     * @return PartnershipDiscount
     */
    public function setIsExpired(bool $isExpired): self
    {
        $this->isExpired = $isExpired;

        return $this;
    }
}
