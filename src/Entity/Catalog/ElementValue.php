<?php

namespace App\Entity\Catalog;

use App\Entity\Field\Field;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalogs_elements_values")
 * @ORM\Entity()
 */
class ElementValue
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="values")
     * @ORM\JoinColumn(name="element_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $element;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Field\Field")
     * @ORM\JoinColumn(name="field_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $field;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * Clone value.
     */
    public function __clone()
    {
        $this->element = null;
    }

    /**
     * @return Element|null
     */
    public function getElement(): ?Element
    {
        return $this->element;
    }

    /**
     * @param Element $element
     *
     * @return ElementValue
     */
    public function setElement(Element $element): self
    {
        $this->element = $element;

        return $this;
    }

    /**
     * @return Field|null
     */
    public function getField(): ?Field
    {
        return $this->field;
    }

    /**
     * @param Field $field
     *
     * @return ElementValue
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
     * @param null|string $value
     *
     * @return ElementValue
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}