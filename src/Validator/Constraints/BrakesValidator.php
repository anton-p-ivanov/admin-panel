<?php
namespace App\Validator\Constraints;

use App\Entity\Price\Brake;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class BrakesValidator
 * @package App\Validator
 */
class BrakesValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param PersistentCollection $value The value that should be validated
     * @param Constraint|Brakes $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var Brake[] $brakes */
        $brakes = $value->toArray();

        foreach ($brakes as $index => $brake) {
            $minLevel = (int) $brake->getPrice()->getMinLevel();

            if ($brake->getMax() > 0 && $brake->getMax() <= $brake->getMin()) {
                $this->context->buildViolation($constraint->message)->addViolation();
                break;
            }

            if ($minLevel > 0 && $brake->getValue() < $minLevel) {
                $this->context->buildViolation($constraint->minLevelMessage)->addViolation();
                break;
            }

            if ($index > 0) {
                $diff = $brakes[$index]->getMin() - $brakes[$index-1]->getMax();

                if ($diff > 1) {
                    $this->context->buildViolation($constraint->gapMessage)->addViolation();
                    break;
                }

                if ($diff < 1) {
                    $this->context->buildViolation($constraint->crossMessage)->addViolation();
                    break;
                }
            }
        }
    }
}