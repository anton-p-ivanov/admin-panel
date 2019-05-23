<?php

namespace App\Entity\Form;

use App\Traits\WorkflowTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="forms_results")
 * @ORM\Entity()
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 */
class Result
{
    use WorkflowTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Form\Form")
     * @ORM\JoinColumn(name="form_uuid", referencedColumnName="uuid")
     */
    private $form;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Form\Status")
     * @ORM\JoinColumn(name="status_uuid", referencedColumnName="uuid")
     */
    private $status;

    /**
     * @ORM\Column(type="text")
     */
    private $data;
}
