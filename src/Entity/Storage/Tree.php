<?php

namespace App\Entity\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="storage_tree")
 * @ORM\Entity(repositoryClass="App\Repository\Storage\StorageTreeRepository")
 * @ORM\EntityListeners({"App\Listener\StorageTreeListener"})
 */
class Tree
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Storage\Storage", inversedBy="nodes", cascade={"persist"})
     * @ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid", unique=false)
     */
    private $storage;

    /**
     * @Gedmo\TreeLeft()
     * @ORM\Column(type="integer")
     */
    private $leftMargin;

    /**
     * @Gedmo\TreeRight()
     * @ORM\Column(type="integer")
     */
    private $rightMargin;

    /**
     * @Gedmo\TreeLevel()
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @Gedmo\TreeRoot()
     * @ORM\ManyToOne(targetEntity="App\Entity\Storage\Tree")
     * @ORM\JoinColumn(name="root_uuid", referencedColumnName="uuid", onDelete="SET NULL")
     */
    private $root;

    /**
     * @Gedmo\TreeParent()
     * @ORM\ManyToOne(targetEntity="App\Entity\Storage\Tree", inversedBy="children")
     * @ORM\JoinColumn(name="parent_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Storage\Tree", mappedBy="parent")
     * @ORM\OrderBy({"leftMargin" = "ASC"})
     */
    private $children;

    /**
     * Tree constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return Tree
     */
    public function getRoot(): Tree
    {
        return $this->root;
    }

    /**
     * @param Tree|null $parent
     *
     * @return Tree
     */
    public function setParent(Tree $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Tree|null
     */
    public function getParent(): ?Tree
    {
        return $this->parent;
    }

    /**
     * @return Storage|null
     */
    public function getStorage(): ?Storage
    {
        return $this->storage;
    }

    /**
     * @param Storage $storage
     *
     * @return Tree
     */
    public function setStorage(Storage $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeftMargin(): int
    {
        return $this->leftMargin;
    }

    /**
     * @return int
     */
    public function getRightMargin(): int
    {
        return $this->rightMargin;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }
}
