<?php

namespace App\Entity\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="catalogs_tree")
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\CatalogTreeRepository")
 * @ORM\EntityListeners({"App\Listener\CatalogTreeListener"})
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
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="nodes", cascade={"persist"})
     * @ORM\JoinColumn(name="element_uuid", referencedColumnName="uuid", nullable=true, unique=false, onDelete="SET NULL")
     */
    private $element;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Tree")
     * @ORM\JoinColumn(name="root_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent()
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Tree", inversedBy="children")
     * @ORM\JoinColumn(name="parent_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Catalog\Tree", mappedBy="parent")
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
    public function getRoot(): self
    {
        return $this->root;
    }

    /**
     * @param Tree $parent
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
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @return Element|null
     */
    public function getElement(): ?Element
    {
        return $this->element;
    }

    /**
     * @param Element $element
     *
     * @return Tree
     */
    public function setElement(Element $element): self
    {
        $this->element = $element;

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
