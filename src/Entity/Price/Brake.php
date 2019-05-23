<?php

namespace App\Entity\Price;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="prices_brakes")
 * @ORM\Entity()
 */
class Brake
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Price\Price", inversedBy="brakes")
     * @ORM\JoinColumn(name="price_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(1)
     */
    private $min;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $max;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type("float")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $value;

    /**
     * Brake constructor.
     */
    public function __construct()
    {
        $properties = [
            'min' => 1,
            'max' => 0,
            'value' => 0,
        ];

        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Brake clone.
     */
    public function __clone()
    {
        $this->uuid = null;
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @param int $min
     *
     * @return Brake
     */
    public function setMin(int $min): self
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param int $max
     *
     * @return Brake
     */
    public function setMax(int $max): self
    {
        $this->max = $max;

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
     * @return Brake
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
     * @return Brake
     */
    public function setPrice(Price $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}
