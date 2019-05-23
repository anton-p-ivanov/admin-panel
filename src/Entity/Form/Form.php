<?php

namespace App\Entity\Form;

use App\Entity\Field\Field;
use App\Entity\Mail\Template;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="forms")
 * @ORM\Entity(repositoryClass="App\Repository\Form\FormRepository")
 * @ORM\EntityListeners({
 *     "App\Listener\WorkflowListener",
 *     "App\Listener\FormListener",
 * })
 * @UniqueEntity("code")
 */
class Form
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $template;

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
     * @ORM\OneToOne(targetEntity="App\Entity\Mail\Template", cascade={"persist"})
     * @ORM\JoinColumn(name="mail_template_uuid", referencedColumnName="uuid", nullable=true, onDelete="SET NULL")
     */
    private $mailTemplate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Form\Status", mappedBy="form", cascade={"persist", "remove"})
     * @var ArrayCollection
     */
    private $statuses;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Field\Field", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *     name="forms_fields",
     *     joinColumns={@ORM\JoinColumn(name="form_uuid", referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid", onDelete="CASCADE")}
     * )
     * @var ArrayCollection
     */
    private $fields;

    /**
     * Form constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'activeFrom' => new \DateTime(),
            'sort' => 100,
            'statuses' => new ArrayCollection(),
            'fields' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Form clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset mail template
        $this->setMailTemplate(null);

        // Unset workflow
        $this->setWorkflow(null);

        // Unset fields collection
        $this->fields = new ArrayCollection();

        // Unset statuses collection
        $statuses = $this->statuses;
        $this->statuses = new ArrayCollection();

        // Clone associations
        if ($this->cloneWithAssociations) {
            foreach ($statuses as $status) {
                $this->addStatus(clone $status);
            }
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
     * @return Form
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
     * @return Form
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
     * @return Form
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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
     * @return Form
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

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
     * @return Form
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
     * @return Form
     */
    public function setActiveTo(?\DateTime $activeTo): self
    {
        $this->activeTo = $activeTo;

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
     * @return Form
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

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
     * @return Form
     */
    public function setMailTemplate(?Template $mailTemplate): self
    {
        $this->mailTemplate = $mailTemplate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return Form
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;

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
     * @return ArrayCollection
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    /**
     * @param Status $status
     *
     * @return Form
     */
    public function addStatus(Status $status): self
    {
        $status->setForm($this);

        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
        }

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
     * @return Form
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
        return strtoupper(md5(get_class($this) . $this->getUuid()));
    }
}
