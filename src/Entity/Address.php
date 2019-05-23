<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * @ORM\Table(name="addresses")
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 */
class Address
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $region;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $district;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length(max="50")
     */
    private $zip;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @CustomAssert\Address()
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var AddressType
     * @ORM\ManyToOne(targetEntity="App\Entity\AddressType")
     * @ORM\JoinColumn(name="type_uuid", referencedColumnName="uuid", nullable=false)
     * @Assert\Type(type="App\Entity\AddressType")
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="code", nullable=false)
     * @Assert\Type(type="App\Entity\Country")
     * @Assert\NotBlank()
     */
    private $country;

    /**
     * Address clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = [
            $this->country->getTitle(),
            $this->zip,
            $this->region,
            $this->district,
            $this->city,
            $this->address
        ];

        return implode(', ', array_filter($parts, 'strlen'));
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
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     *
     * @return Address
     */
    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDistrict(): ?string
    {
        return $this->district;
    }

    /**
     * @param string|null $district
     *
     * @return Address
     */
    public function setDistrict(?string $district): self
    {
        $this->district = $district;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return Address
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @param string|null $zip
     *
     * @return Address
     */
    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return Address
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return AddressType|null
     */
    public function getType(): ?AddressType
    {
        return $this->type;
    }

    /**
     * @param AddressType $type
     *
     * @return Address
     */
    public function setType(AddressType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     *
     * @return Address
     */
    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }
}
