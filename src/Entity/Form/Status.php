<?php

namespace App\Entity\Form;

use App\Entity\Mail\Template;
use App\Traits\WorkflowTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="forms_statuses")
 * @ORM\Entity(repositoryClass="App\Repository\Form\StatusRepository")
 * @ORM\EntityListeners({
 *     "App\Listener\WorkflowListener",
 *     "App\Listener\FormStatusListener"
 * })
 */
class Status
{
    use WorkflowTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

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
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isDefault;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Form\Form", inversedBy="statuses")
     * @ORM\JoinColumn(name="form_uuid", referencedColumnName="uuid")
     */
    private $form;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Mail\Template", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="mail_template_uuid", referencedColumnName="uuid", nullable=true, onDelete="SET NULL")
     */
    private $mailTemplate;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isDefault' => false,
            'sort' => 100,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Status clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset mail template
        $this->setMailTemplate(null);

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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Status
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
     * @return Status
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     * @return Status
     */
    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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
     * @param int $sort
     *
     * @return Status
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return Template|null
     */
    public function getMailTemplate(): ?Template
    {
        return $this->mailTemplate;
    }

    /**
     * @param Template|null $mailTemplate
     *
     * @return Status
     */
    public function setMailTemplate(?Template $mailTemplate): self
    {
        $this->mailTemplate = $mailTemplate;

        return $this;
    }

    /**
     * @return Form|null
     */
    public function getForm(): ?Form
    {
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return Status
     */
    public function setForm(Form $form): self
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault === true;
    }
}
