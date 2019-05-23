<?php

namespace App\Entity\Field;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * @ORM\Table(name="fields_validators")
 * @ORM\Entity(repositoryClass="App\Repository\Field\ValidatorRepository")
 */
class Validator
{
    /**
     * Field validator types constants
     */
    const
        TYPE_STRING = 'S',
        TYPE_BOOLEAN = 'B',
        TYPE_DATE = 'D',
        TYPE_NUMBER = 'N',
        TYPE_EMAIL = 'E',
        TYPE_MATCH = 'M',
        TYPE_REQUIRED = 'R',
        TYPE_URL = 'W',
        TYPE_UNIQUE = 'U',
        TYPE_FILE = 'F';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=1, options={"fixed":true})
     * @Assert\Choice(callback="getTypes")
     */
    private $type;

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
    private $isActive;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Field\Field", inversedBy="validators")
     * @ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid")
     */
    private $field;

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            'Boolean' => self::TYPE_BOOLEAN,
            'Date' => self::TYPE_DATE,
            'Number' => self::TYPE_NUMBER,
            'E-Mail' => self::TYPE_EMAIL,
            'Regular expression' => self::TYPE_MATCH,
            'Required' => self::TYPE_REQUIRED,
            'String' => self::TYPE_STRING,
            'Url' => self::TYPE_URL,
            'Unique' => self::TYPE_UNIQUE,
            'File' => self::TYPE_FILE,
        ];
    }

    /**
     * @param $type
     *
     * @return string
     */
    public static function getTypeName($type): string
    {
        return array_flip(self::getTypes())[$type];
    }

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'type' => self::TYPE_STRING,
            'sort' => 100
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Validator clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
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
     * @return Validator
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return null|Field
     */
    public function getField(): ?Field
    {
        return $this->field;
    }

    /**
     * @param Field $field
     *
     * @return Validator
     */
    public function setField(Field $field): self
    {
        $this->field = $field;

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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Validator
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
     * @return Validator
     */
    public function setOptions(?string $options): self
    {
        $this->options = $options;

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
     * @return Validator
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
