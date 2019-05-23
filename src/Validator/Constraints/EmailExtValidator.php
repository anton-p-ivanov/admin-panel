<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EmailValidator
 * @package App\Validator
 */
class EmailExtValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^{{\w+}}$/', $value, $matches)) {
            $validator = new EmailValidator();
            $validator->context = $this->context;
            $validator->validate($value, new Email());
        }
    }
}