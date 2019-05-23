<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="sales_discounts")
 * @ORM\Entity(repositoryClass="App\Repository\SalesDiscountRepository")
 * @UniqueEntity("code")
 */
class SalesDiscount
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\Slug(fields={"title"}, updatable=false, separator="_", style="upper")
     * @Assert\Length(max="255")
     * @Assert\Regex("/^\w+$/")
     */
    private $code;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=4)
     * @Assert\Range(min="0", max="1", minMessage="Значение должно быть от 0 до 100.", maxMessage="Значение должно быть от 0 до 100.")
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\Type("numeric")
     */
    private $sort;

    /**
     * SaleDiscount constructor.
     */
    public function __construct()
    {
        $defaults = [
            'sort' => 100,
            'value' => 0
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * SaleDiscount clone.
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
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return SalesDiscount
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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
     * @return SalesDiscount
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

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
     * @return SalesDiscount
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

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
     * @return SalesDiscount
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }
}
