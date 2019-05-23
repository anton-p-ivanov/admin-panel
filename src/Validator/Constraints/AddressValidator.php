<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AddressValidator
 * @package App\Validator
 */
class AddressValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $abbreviations = [
        'обл.', 'пл.', 'р-н', 'б-р', 'г.', 'линия', 'пос.', 'ш.',
        'ул.', 'д.', 'пр-т', 'корп.', 'пр.', 'стр.', 'пер.', 'эт.',
        'наб.', 'кв.', 'оф.', 'пом.',
    ];

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint|Address $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        // ул. Ленина, д. 1, корп. 3, кв. 7
        $portions = preg_split('/,[\s]*/', $value);
        foreach ($portions as $portion) {
            $parts = preg_split('/\s/', $portion);
            if (count($parts) !== 2) {
                $this->context->buildViolation($constraint->message)->addViolation();
                break;
            }

            if (!$parts[1] || !in_array($parts[0], $this->abbreviations)) {
                $this->context->buildViolation($constraint->message)->addViolation();
                break;
            }
        }
    }
}