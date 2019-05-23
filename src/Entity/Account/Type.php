<?php

namespace App\Entity\Account;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="accounts_types")
 * @ORM\Entity(repositoryClass="App\Repository\Account\TypeRepository")
 * @ORM\EntityListeners({"App\Listener\AccountTypeListener"})
 */
class Type
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $sort;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isDefault;

    /**
     * Type constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isDefault' => false,
            'sort' => 100
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Type clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Prevent changing default state
        $this->isDefault = false;
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
     * @return Type
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

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
     * @return Type
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     *
     * @return Type
     */
    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault === true;
    }
}
