<?php

namespace App\Entity\Catalog;

use App\Entity\Field\Field;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="catalogs")
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\CatalogRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 * @UniqueEntity("code")
 */
class Catalog
{
    use WorkflowTrait;

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
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isTrading;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Type", inversedBy="catalogs")
     * @ORM\JoinColumn(name="type_uuid", referencedColumnName="uuid")
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Catalog\Tree", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="tree_uuid", referencedColumnName="uuid", onDelete="SET NULL")
     */
    private $tree;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Site", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="catalogs_sites",
     *     joinColumns={@ORM\JoinColumn(name="catalog_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="site_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     */
    private $sites;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Field\Field", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *     name="catalogs_fields",
     *     joinColumns={@ORM\JoinColumn(name="catalog_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     * @var ArrayCollection
     */
    private $fields;

    /**
     * @ORM\OneToMany(targetEntity="Element", mappedBy="catalog")
     */
    private $elements;

    /**
     * Catalog constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'isTrading' => false,
            'sort' => 100,
            'tree' => new Tree(),
            'sites' => new ArrayCollection(),
            'fields' => new ArrayCollection(),
            'elements' => new ArrayCollection(),
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Catalog clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->setWorkflow(null);

        // Unset tree
        $this->setTree(new Tree());

        // Unset fields collection
        $this->fields = new ArrayCollection();
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
     * @return Catalog
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
     * @return Catalog
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
     * @return Catalog
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
     * @return Catalog
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
     * @return Catalog
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsTrading(): bool
    {
        return $this->isTrading;
    }

    /**
     * @param bool $isTrading
     *
     * @return Catalog
     */
    public function setIsTrading(bool $isTrading): self
    {
        $this->isTrading = $isTrading;

        return $this;
    }

    /**
     * @return Tree|null
     */
    public function getTree(): ?Tree
    {
        return $this->tree;
    }

    /**
     * @param Tree $tree
     *
     * @return Catalog
     */
    public function setTree(Tree $tree): self
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return Catalog
     */
    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    /**
     * @param ArrayCollection $sites
     *
     * @return Catalog
     */
    public function setSites(ArrayCollection $sites): self
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @param Field $field
     *
     * @return Catalog
     */
    public function addField(Field $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return strtoupper(md5(get_class($this).$this->getUuid()));
    }

    /**
     * @return ArrayCollection
     */
    public function getElements(): Collection
    {
        return $this->elements;
    }

    /**
     * @param ArrayCollection $elements
     *
     * @return Catalog
     */
    public function setElements(ArrayCollection $elements): self
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive === true;
    }

    /**
     * @return bool
     */
    public function isTrading(): bool
    {
        return $this->isTrading === true;
    }
}
