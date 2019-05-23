<?php

namespace App\Entity\Price;

use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="prices")
 * @ORM\Entity()
 */
class Price
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $minLevel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Price\Currency")
     * @ORM\JoinColumn(name="currency", referencedColumnName="uuid")
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Price\Vat")
     * @ORM\JoinColumn(name="vat", referencedColumnName="uuid")
     */
    private $vat;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Price\Brake", mappedBy="price", cascade={"persist"})
     * @ORM\OrderBy({"min": "ASC"})
     * @Assert\Valid()
     * @CustomAssert\Brakes()
     * @var ArrayCollection
     */
    private $brakes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Price\Discount", mappedBy="price", cascade={"persist"})
     * @ORM\OrderBy({"createdAt": "DESC"})
     * @var ArrayCollection
     */
    private $discounts;

    /**
     * Price constructor.
     */
    public function __construct()
    {
        $properties = [
            'minLevel' => 0.0,
            'brakes' => new ArrayCollection([new Brake()]),
            'discounts' => new ArrayCollection()
        ];

        foreach ($properties as $property => $value) {
            $this->{'set' . ucfirst($property)}($value);
        }
    }

    /**
     * Price clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Cloning price brakes
        $this->setBrakes($this->cloneBrakes());

        // Cloning price discounts
        $this->setDiscounts($this->cloneDiscounts());
    }

    /**
     * @return float
     */
    public function getMinLevel(): float
    {
        return $this->minLevel;
    }

    /**
     * @param float $minLevel
     *
     * @return Price
     */
    public function setMinLevel(float $minLevel): self
    {
        $this->minLevel = $minLevel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param null|string $comments
     *
     * @return Price
     */
    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return Currency|null
     */
    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     *
     * @return Price
     */
    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Vat|null
     */
    public function getVat(): ?Vat
    {
        return $this->vat;
    }

    /**
     * @param Vat $vat
     *
     * @return Price
     */
    public function setVat(Vat $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return ArrayCollection
     */
    public function getBrakes(): Collection
    {
        return $this->brakes;
    }

    /**
     * @param Collection $brakes
     */
    public function setBrakes(Collection $brakes)
    {
        foreach ($brakes as $brake) {
            $brake->setPrice($this);
        }

        $this->brakes = $brakes;
    }

    /**
     * @return ArrayCollection
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    /**
     * @param Collection $discounts
     */
    public function setDiscounts(Collection $discounts)
    {
        $this->discounts = $discounts;
    }

    /**
     * @return Collection
     */
    private function cloneBrakes(): Collection
    {
        $clonedBrakes = new ArrayCollection();

        foreach ($this->brakes as $brake) {
            $clonedBrakes->add(clone $brake);
        }

        return $clonedBrakes;
    }

    /**
     * @return Collection
     */
    private function cloneDiscounts(): Collection
    {
        $discounts = new ArrayCollection();

        foreach ($this->discounts as $discount) {
            $clone = clone $discount;
            $clone->setPrice($this);

            $discounts->add($clone);
        }

        return $discounts;
    }
}
