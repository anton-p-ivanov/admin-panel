<?php

namespace App\Entity\Field;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="fields_values")
 * @ORM\Entity(repositoryClass="App\Repository\Field\ValueRepository")
 */
class Value
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(max="255")
     */
    private $value;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(max="255")
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Field\Field", inversedBy="values")
     * @ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid")
     */
    private $field;

    /**
     * Value constructor.
     */
    public function __construct()
    {
        $defaults = [
            'sort' => 100,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Value clone.
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
     * @return Value
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
     * @return Value
     */
    public function setField(Field $field): self
    {
        $this->field = $field;

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
     * @param string $value
     *
     * @return Value
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Value
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
