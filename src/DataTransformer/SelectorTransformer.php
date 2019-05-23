<?php

namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class SelectorTransformer
 * @package App\DataTransformer
 */
class SelectorTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (is_object($value)) {
            return method_exists($value, 'getUuid') ? $value->getUuid() : null;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        return $value;
    }
}