<?php

namespace App\Entity\Mail;

use App\Entity\Site;
use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * @ORM\Table(name="mail_templates")
 * @ORM\Entity(repositoryClass="App\Repository\Mail\TemplateRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 */
class Template
{
    use WorkflowTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string",  length=255, unique=true)
     * @Gedmo\Slug(fields={"subject"}, updatable=false, separator="_", style="upper")
     * @Assert\Length(max="255")
     * @Assert\Regex("/^\w+$/")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @AppAssert\EmailExt()
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @AppAssert\EmailExt()
     */
    private $recipient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @AppAssert\EmailExt()
     */
    private $replyTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @AppAssert\EmailExt()
     */
    private $copyTo;

    /**
     * @ORM\Column(type="boolean", options={"default" : 1})
     * @Assert\Type(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $subject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $textBody;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $htmlBody;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mail\Type", inversedBy="templates", cascade={"persist"})
     * @ORM\JoinColumn(name="type_uuid", referencedColumnName="uuid", nullable=false)
     * @Assert\Type(type="App\Entity\Mail\Type")
     */
    private $type;

    /**
     * Unidirectional many-to-many association.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Site")
     * @ORM\JoinTable(
     *     name="mail_templates_sites",
     *     joinColumns={@ORM\JoinColumn(name="template_uuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="site_uuid", referencedColumnName="uuid")}
     * )
     */
    private $sites;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->isActive = true;
        $this->sites = new ArrayCollection();
    }

    /**
     * Template clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

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
     * @return null|string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Template
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * @param string|null $sender
     *
     * @return Template
     */
    public function setSender(?string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     *
     * @return Template
     */
    public function setRecipient(string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * @param null|string $replyTo
     *
     * @return Template
     */
    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCopyTo(): ?string
    {
        return $this->copyTo;
    }

    /**
     * @param null|string $copyTo
     *
     * @return Template
     */
    public function setCopyTo(?string $copyTo): self
    {
        $this->copyTo = $copyTo;

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
     * @return Template
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return Template
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    /**
     * @param null|string $textBody
     *
     * @return Template
     */
    public function setTextBody(?string $textBody): self
    {
        $this->textBody = $textBody;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    /**
     * @param null|string $htmlBody
     *
     * @return Template
     */
    public function setHtmlBody(?string $htmlBody): self
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    /**
     * @return Type|null
     */
    public function getType(): ?Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return Template
     */
    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Site[]
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    /**
     * @param Site[] $sites
     *
     * @return Template
     */
    public function setSites(array $sites): self
    {
        $this->sites = $sites;

        return $this;
    }
}
