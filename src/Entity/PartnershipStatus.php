<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="partnership_statuses")
 * @ORM\Entity(repositoryClass="App\Repository\PartnershipStatusRepository")
 * @UniqueEntity("code")
 */
class PartnershipStatus
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
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
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PartnershipDiscount", mappedBy="status", cascade={"persist"})
     * @var ArrayCollection
     */
    private $discounts;

    /**
     * PartnershipStatus constructor.
     */
    public function __construct()
    {
        $defaults = [
            'sort' => 100,
            'discounts' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * PartnershipStatus clone.
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
     * @return PartnershipStatus
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
     * @return PartnershipStatus
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return PartnershipStatus
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

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
     * @return PartnershipStatus
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDiscounts(): ArrayCollection
    {
        return $this->discounts;
    }

    /**
     * @param PartnershipDiscount $discount
     *
     * @return PartnershipStatus
     */
    public function addDiscount(PartnershipDiscount $discount): self
    {
        $discount->setStatus($this);

        if (!$this->discounts->contains($discount)) {
            $this->discounts->add($discount);
        }

        return $this;
    }
}
