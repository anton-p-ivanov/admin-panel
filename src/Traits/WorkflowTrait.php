<?php

namespace App\Traits;

use App\Entity\Workflow;
use App\Entity\WorkflowStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait WorkflowTrait
 * @package App\Traits
 */
trait WorkflowTrait
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Workflow", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="workflow_uuid", referencedColumnName="uuid", nullable=true, onDelete="SET NULL")
     */
    private $workflow;

    /**
     * @var WorkflowStatus
     */
    private $status;

    /**
     * @return Workflow|null
     */
    public function getWorkflow(): ?Workflow
    {
        return $this->workflow;
    }

    /**
     * @param mixed $workflow
     *
     * @return mixed
     */
    public function setWorkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * @return WorkflowStatus|null
     */
    public function getStatus(): ?WorkflowStatus
    {
        if ($this->status === null) {
            $this->status = $this->getWorkflow()
                ? $this->getWorkflow()->getStatus()
                : null;
        }

        return $this->status;
    }

    /**
     * @param WorkflowStatus $status
     *
     * @return mixed
     */
    public function setStatus(WorkflowStatus $status): self
    {
        $this->getWorkflow()->setStatus($status);

        return $this;
    }
}