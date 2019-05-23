<?php

namespace App\Entity\Catalog;

use App\Entity\Price\Price;
use App\Entity\Workflow;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="catalogs_elements", uniqueConstraints={@UniqueConstraint(columns={"code", "catalog_uuid"})})
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\CatalogElementRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 * @UniqueEntity({"code", "catalog"})
 */
class Element
{
    use WorkflowTrait;

    const TYPE_ELEMENT = 'E';
    const TYPE_SECTION = 'S';

    /**
     * @var bool
     */
    public $cloneWithAssociations = false;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=1, options={"fixed" = true})
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getTypes")
     */
    private $type;

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
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

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
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $activeFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $activeTo;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Catalog", inversedBy="elements")
     * @ORM\JoinColumn(name="catalog_uuid", referencedColumnName="uuid", nullable=true, onDelete="SET NULL")
     */
    private $catalog;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Catalog\Tree", mappedBy="element")
     */
    private $nodes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Site")
     * @ORM\JoinTable(
     *     name="catalogs_elements_sites",
     *     joinColumns={@ORM\JoinColumn(name="element_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="site_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     */
    private $sites;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role")
     * @ORM\JoinTable(
     *     name="catalogs_elements_roles",
     *     joinColumns={@ORM\JoinColumn(name="element_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     */
    private $roles;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Price\Price", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="catalogs_elements_prices",
     *     joinColumns={@ORM\JoinColumn(name="element_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="price_uuid", referencedColumnName="uuid", unique=true, onDelete="CASCADE")}
     * )
     */
    private $prices;

    /**
     * @Assert\Type(type="App\Entity\Price\Price")
     * @Assert\Valid()
     */
    private $price;

    /**
     * @var ArrayCollection
     */
    private $parentNodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Catalog\ElementValue", indexBy="field_uuid", mappedBy="element", cascade={"persist"})
     * @Assert\Valid()
     * @var ArrayCollection
     */
    private $values;

    /**
     * Element constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'activeFrom' => new \DateTime(),
            'sort' => 100,
            'type' => self::TYPE_ELEMENT,
            'sites' => new ArrayCollection(),
            'roles' => new ArrayCollection(),
            'nodes' => new ArrayCollection(),
            'prices' => new ArrayCollection(),
            'values' => new ArrayCollection(),
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Element clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Set workflow
        $workflow = new Workflow();
        $workflow->setStatus($this->getWorkflow()->getStatus());

        $this->setWorkflow($workflow);

        if ($this->cloneWithAssociations) {
            // Clone price
            if ($price = $this->getPrice()) {
                $this->prices = new ArrayCollection([clone $price]);
            }

            // Clone values
            foreach ($this->values as $value) {
                $this->addValue(clone $value);
            }
        }
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_ELEMENT,
            self::TYPE_SECTION,
        ];
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
     * @return Element
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
     * @param string|null $title
     *
     * @return Element
     */
    public function setTitle(?string $title): self
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
     * @return Element
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param null|string $content
     *
     * @return Element
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive === true && ($this->activeTo === null || $this->activeTo > new \DateTime());
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
     * @return Element
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
     * @return Element
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getActiveFrom(): ?\DateTime
    {
        return $this->activeFrom;
    }

    /**
     * @param \DateTime|null $activeFrom
     *
     * @return Element
     */
    public function setActiveFrom(?\DateTime $activeFrom): self
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getActiveTo(): ?\DateTime
    {
        return $this->activeTo;
    }

    /**
     * @param \DateTime|null $activeTo
     *
     * @return Element
     */
    public function setActiveTo(?\DateTime $activeTo): self
    {
        $this->activeTo = $activeTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Element
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Catalog|null
     */
    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    /**
     * @param Catalog $catalog
     *
     * @return Element
     */
    public function setCatalog(Catalog $catalog): self
    {
        $this->catalog = $catalog;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    /**
     * @return ArrayCollection
     */
    public function getParentNodes(): Collection
    {
        if ($this->parentNodes === null) {
            $this->parentNodes = new ArrayCollection();

            if ($nodes = $this->getNodes()) {
                foreach ($nodes as $node) {
                    $this->parentNodes->add($node->getParent());
                }
            }
        }

        return $this->parentNodes;
    }

    /**
     * @param ArrayCollection $nodes
     *
     * @return Element
     */
    public function setParentNodes(ArrayCollection $nodes): self
    {
        // Filter out null items
        $nodes = $nodes->filter(
            function ($node) {
                return $node !== null;
            }
        );

        if ($nodes->isEmpty()) {
            $nodes = new ArrayCollection([$this->getCatalog()->getTree()]);
        }

        $this->parentNodes = $nodes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSection(): bool
    {
        return $this->type === self::TYPE_SECTION;
    }

    /**
     * @return ArrayCollection
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    /**
     * @param ArrayCollection|Collection $sites
     *
     * @return Element
     */
    public function setSites(Collection $sites): self
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @param ArrayCollection|Collection $roles
     *
     * @return Element
     */
    public function setRoles(Collection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Price|null
     */
    public function getPrice(): ?Price
    {
        if (!$this->prices->isEmpty()) {
            $this->price = $this->prices->first();
        }

        return $this->price;
    }

    /**
     * @param Price $price
     *
     * @return Element
     */
    public function setPrice(Price $price): self
    {
        $this->price = $price;

        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    /**
     * @param Collection $values
     *
     * @return Element
     */
    public function setValues(Collection $values): self
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param ElementValue $value
     *
     * @return Element
     */
    public function addValue(ElementValue $value): self
    {
        if (!$this->values->contains($value)) {
            $value->setElement($this);
            $this->values->add($value);
        }

        return $this;
    }
}
