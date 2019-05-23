<?php

namespace App\Entity\Field;

use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @ORM\Table(name="fields", uniqueConstraints={@UniqueConstraint(name="UNIQ_7EE5E38877153098", columns={"code", "hash"})})
 * @ORM\Entity(repositoryClass="App\Repository\Field\FieldRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 * @UniqueEntity({"code", "hash"})
 * @Assert\GroupSequenceProvider()
 */
class Field implements GroupSequenceProviderInterface
{
    use WorkflowTrait;

    /**
     * Constants
     */
    const
        FIELD_TYPE_DEFAULT = 'S',
        FIELD_TYPE_STRING = 'S',
        FIELD_TYPE_TEXT = 'T',
        FIELD_TYPE_CHOICE = 'C',
        FIELD_TYPE_ENTITY = 'E',
        FIELD_TYPE_FILE = 'F',
        FIELD_TYPE_ACCOUNT = 'A',
        FIELD_TYPE_USER = 'U',
        FIELD_TYPE_COUNTRY = 'Y';

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
    private $label;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max="1000")
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Slug(fields={"label"}, updatable=false, separator="_", style="upper")
     * @Assert\Length(max="255")
     * @Assert\Regex("/^\w+$/")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=32, options={"fixed":true})
     */
    private $hash;

    /**
     * @ORM\Column(type="string", length=1, options={"fixed":true})
     * @Assert\Choice(callback="getTypesKeys")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $value;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max="1000")
     * @AppAssert\Json()
     */
    private $options;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isMultiple;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isExpanded;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $inList;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Field\Validator", mappedBy="field", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid")
     * @var ArrayCollection
     */
    private $validators;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Field\Value", mappedBy="field", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid")
     * @var ArrayCollection
     */
    private $values;

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::FIELD_TYPE_STRING => 'String',
            self::FIELD_TYPE_TEXT => 'Text',
            self::FIELD_TYPE_CHOICE => 'Choice',
            self::FIELD_TYPE_ENTITY => 'Entity',
            self::FIELD_TYPE_FILE => 'File',
            self::FIELD_TYPE_ACCOUNT => 'Account',
            self::FIELD_TYPE_USER => 'User',
            self::FIELD_TYPE_COUNTRY => 'Country',
        ];
    }

    /**
     * @return array
     */
    public static function getTypesKeys(): array
    {
        return array_keys(self::getTypes());
    }

    /**
     * Field constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'inList' => false,
            'type' => self::FIELD_TYPE_DEFAULT,
            'isMultiple' => false,
            'isExpanded' => false,
            'sort' => 100,
            'validators' => new ArrayCollection(),
            'values' => new ArrayCollection(),
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Field clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->setWorkflow(null);

        // Clone associations
        if ($this->cloneWithAssociations) {
            foreach ($this->validators as $value) {
                $this->addValidator(clone $value);
            }

            foreach ($this->values as $value) {
                $this->addValue(clone $value);
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
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Field
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

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
     * @return Field
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param null|string $value
     *
     * @return Field
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

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
     * @return Field
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return Field
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive === true;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->isMultiple === true;
    }

    /**
     * @return bool
     */
    public function isExpanded(): bool
    {
        return $this->isExpanded === true;
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
     * @return Field
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

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
     * @param mixed $type
     *
     * @return Field
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOptions(): ?string
    {
        return $this->options;
    }

    /**
     * @param null|string $options
     *
     * @return Field
     */
    public function setOptions(?string $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return bool
     */
    public function getInList(): bool
    {
        return $this->inList;
    }

    /**
     * @param bool $inList
     *
     * @return Field
     */
    public function setInList(bool $inList): self
    {
        $this->inList = $inList;

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
     * @return Field
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMultiple(): bool
    {
        return $this->isMultiple;
    }

    /**
     * @param bool $isMultiple
     *
     * @return Field
     */
    public function setIsMultiple(bool $isMultiple): self
    {
        $this->isMultiple = $isMultiple;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExpanded(): bool
    {
        return $this->isExpanded;
    }

    /**
     * @param bool $isExpanded
     *
     * @return Field
     */
    public function setIsExpanded(bool $isExpanded): self
    {
        $this->isExpanded = $isExpanded;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getValidators(): Collection
    {
        return $this->validators;
    }

    /**
     * @param Validator $validator
     *
     * @return Field
     */
    public function addValidator(Validator $validator): self
    {
        $validator->setField($this);

        if (!$this->validators->contains($validator)) {
            $this->validators->add($validator);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function getValuesArray()
    {
        $result = [];
        foreach ($this->values as $value) {
            $result[$value->getLabel()] = $value->getValue();
        }

        return $result;
    }

    /**
     * @param Value $value
     *
     * @return Field
     */
    public function addValue(Value $value): self
    {
        $value->setField($this);

        if (!$this->values->contains($value)) {
            $this->values->add($value);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSelectable(): bool
    {
        return in_array($this->type, [
            self::FIELD_TYPE_CHOICE,
            self::FIELD_TYPE_USER,
            self::FIELD_TYPE_ACCOUNT,
            self::FIELD_TYPE_COUNTRY,
        ]);
    }

    /**
     * @return array
     */
    public function getGroupSequence(): array
    {
        $groups = ['Field'];

        if ($this->type === self::FIELD_TYPE_ENTITY) {
            $groups[] = 'entity';
        }

        return $groups;
    }
}
