<?php

namespace App\Traits;

use Symfony\Component\Form\FormInterface;

/**
 * Trait AjaxControllerTrait
 * @package App\Traits
 */
trait AjaxControllerTrait
{
    /**
     * @param FormInterface $form
     * @param null|string $parentName
     * @param array $errors
     *
     * @return array
     */
    protected function getFormErrors(FormInterface $form, $parentName = null, &$errors = [])
    {
        $name = $parentName ? $parentName . '_'. $form->getName() : $form->getName();

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$name . '_' . $child->getName()][] = $error->getMessage();
                }

                $this->getFormErrors($child, $name, $errors);
            }
        }

        return $errors;
    }
}