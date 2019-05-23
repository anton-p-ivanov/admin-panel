<?php

namespace App\Entity;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="workflow")
 * @ORM\Entity()
 */
class Workflow
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="guid", nullable=true)
     */
    private $statusUuid;

    /**
     * @ORM\Column(type="guid", nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="guid", nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean", name="is_deleted", options={"default":false})
     */
    private $isDeleted;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", cascade={"persist"})
     * @ORM\JoinColumn(name="created_by", referencedColumnName="uuid", onDelete="SET NULL")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", cascade={"persist"})
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="uuid", onDelete="SET NULL")
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkflowStatus", cascade={"persist"})
     * @ORM\JoinColumn(name="status_uuid", referencedColumnName="uuid", onDelete="SET NULL")
     */
    private $status;

    /**
     * Workflow constructor.
     */
    public function __construct()
    {
        $defaults = [
            'createdAt' => new \DateTime(),
            'updatedAt' => new \DateTime(),
            'isDeleted' => false
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Workflow
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     *
     * @return Workflow
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return WorkflowStatus|null
     */
    public function getStatus(): ?WorkflowStatus
    {
        return $this->status;
    }

    /**
     * @param WorkflowStatus|null $status
     *
     * @return Workflow
     */
    public function setStatus(?WorkflowStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreated(): ?User
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     *
     * @return Workflow
     */
    public function setCreated(?User $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUpdated(): ?User
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     *
     * @return Workflow
     */
    public function setUpdated(?User $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatusUuid(): ?string
    {
        return $this->statusUuid;
    }

    /**
     * @return string|null
     */
    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    /**
     * @return string|null
     */
    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    /**
     * @param array $properties
     */
    public function markAsDeleted(array $properties = [])
    {
        $this->isDeleted = true;

        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted === true;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->getStatus()->getCode() === 'PUBLISHED';
    }
}
