<?php

namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Json extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Значение должно быть валидной JSON-строкой.';
}