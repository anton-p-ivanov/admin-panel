<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class JsonValidator
 * @package App\Validator
 */
class JsonValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        $json = json_decode($value);
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->context->buildViolation($constraint->{'message'})
                ->addViolation();
        }
    }
}