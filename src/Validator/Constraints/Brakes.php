<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Brakes extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Диапазоны цен заданы неверно.';
    /**
     * @var string
     */
    public $crossMessage = 'Диапазоны min-max не должны пересекаться.';
    /**
     * @var string
     */
    public $gapMessage = 'Между диапазонами min-max не должно быть разрывов.';
    /**
     * @var string
     */
    public $minLevelMessage = 'Цена не может быть меньше MinLevel.';
}