<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Address extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Адрес указан неверно.';
}