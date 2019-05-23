<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="workflow_statuses")
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowStatusRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowStatusListener"})
 */
class WorkflowStatus
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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $title;

    /**
     * @ORM\Column(type="integer", options={"default" : 100})
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     * @Assert\Type("boolean")
     */
    private $isDefault;

    /**
     * WorkflowStatus constructor.
     */
    public function __construct()
    {
        $this->sort = 100;
        $this->isDefault = false;
    }

    /**
     * Status clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Prevent changing default status on cloning
        $this->isDefault = false;
    }

    /**
     * @return string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param null|string $code
     *
     * @return WorkflowStatus
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

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
     * @param string $sort
     *
     * @return WorkflowStatus
     */
    public function setSort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return WorkflowStatus
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

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
     * @return WorkflowStatus
     */
    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }
}
