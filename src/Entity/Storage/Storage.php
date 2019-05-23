<?php

namespace App\Entity\Storage;

use App\Entity\Role;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="storage")
 * @ORM\Entity()
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 */
class Storage
{
    use WorkflowTrait;

    /**
     * Storage type `file`
     */
    const STORAGE_TYPE_FILE = 'F';

    /**
     * Storage type `directory`
     */
    const STORAGE_TYPE_DIR = 'D';

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
     * @ORM\Column(type="string", length=255)
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Role")
     * @ORM\JoinTable(
     *     name="storage_roles",
     *     joinColumns={@ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     */
    private $roles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Storage\Category")
     * @ORM\JoinTable(
     *     name="storage_categories_pivot",
     *     joinColumns={@ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_uuid", referencedColumnName="uuid")}
     * )
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Storage\Tree", mappedBy="storage")
     */
    private $nodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Storage\Version", mappedBy="storage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid")
     * @var ArrayCollection
     */
    private $versions;

    /**
     * @var File|null
     */
    private $file;

    /**
     * @var ArrayCollection
     */
    private $parentNodes;

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::STORAGE_TYPE_DIR,
            self::STORAGE_TYPE_FILE,
        ];
    }

    /**
     * Storage constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $defaults = [
            'type' => self::STORAGE_TYPE_DIR,
            'versions' => new ArrayCollection(),
            'roles' => new ArrayCollection(),
            'sites' => new ArrayCollection()
        ];

        $attributes = array_merge($defaults, $attributes);

        foreach ($attributes as $attribute => $value) {
            $this->$attribute = $value;
        }
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
     * @return Storage
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
     * @return Storage
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     * @return Storage
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDirectory(): bool
    {
        return $this->type === self::STORAGE_TYPE_DIR;
    }

    /**
     * @return ArrayCollection|Version[]
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    /**
     * @param File $file
     *
     * @return Storage
     */
    public function addVersion(File $file): self
    {
        $version = new Version();
        $version->setFile($file);
        $version->setStorage($this);

        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        $versions = $this->versions->filter(function (Version $version) {
            return $version->isActive();
        });

        if (!$versions->isEmpty()) {
            return $versions->first()->getFile();
        }

        return null;
    }

    /**
     * @param File $file
     *
     * @return Storage
     */
    public function setFile(File $file): self
    {
        $this->file = $file;

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
                    if ($parentNode = $node->getParent()) {
                        $this->parentNodes->add($parentNode);
                    }
                }
            }
        }

        return $this->parentNodes;
    }

    /**
     * @param ArrayCollection|null $nodes
     *
     * @return Storage
     */
    public function setParentNodes(?ArrayCollection $nodes): self
    {
        if ($nodes) {
            // Filter out null items
            $nodes = $nodes->filter(
                function ($node) {
                    return $node !== null;
                }
            );
        }

        $this->parentNodes = $nodes;

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @param Collection $roles
     *
     * @return Storage
     */
    public function setRoles(Collection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
