<?php

namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailExt extends Constraint
{
    public $message = 'Значение должно быть валидным E-Mail или одним из допустимых шаблонов.';
}