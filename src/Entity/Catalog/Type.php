<?php

namespace App\Entity\Catalog;

use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="catalogs_types")
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\TypeRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 * @UniqueEntity("code")
 */
class Type
{
    use WorkflowTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="text", length=1000, nullable=true)
     * @Assert\Length(max="1000")
     */
    private $description;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\Slug(fields={"title"}, updatable=false, separator="_", style="upper")
     * @Assert\Length(max="255")
     * @Assert\Regex("/^\w+$/")
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Catalog\Catalog", mappedBy="type")
     */
    private $catalogs;

    /**
     * Type constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'sort' => 100,
            'catalogs' => new ArrayCollection(),
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

        // Unset workflow
        $this->setWorkflow(null);
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
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
     * @return null|string
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
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return Type
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Type
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
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
     * @return Type
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCatalogs(): Collection
    {
        return $this->catalogs;
    }

    /**
     * @param ArrayCollection $catalogs
     *
     * @return Type
     */
    public function setCatalogs(ArrayCollection $catalogs): self
    {
        $this->catalogs = $catalogs;

        return $this;
    }
}
