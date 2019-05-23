<?php

namespace App\Form\Training;

use App\Entity\Training\Answer;
use App\Entity\Training\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AnswerType
 * @package App\Form\Training
 */
class AnswerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        if ($entity->getUuid() === null) {
            $entity->setIsValid($this->getCheckedState($entity));
        }

        $builder
            ->add('isValid', CheckboxType::class, [
                'required' => false,
                'disabled' => $this->getDisabledState($entity)
            ])
            ->add('answer', TextareaType::class, ['attr' => ['rows' => 5]])
            ->add('sort', TextType::class, ['required' => false])
            ->add('submit', SubmitType::class)
            ->add('apply', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Answer::class
        ]);
    }

    /**
     * @param Answer $answer
     *
     * @return bool
     */
    private function getDisabledState(Answer $answer): bool
    {
        return $answer->isValid() && $answer->getQuestion()->getType() === Question::TYPE_SINGLE
            || $this->getCheckedState($answer);
    }

    /**
     * @param Answer $answer
     *
     * @return bool
     */
    private function getCheckedState(Answer $answer): bool
    {
        return $answer->getUuid() === null && $answer->getQuestion()->getAnswers()->isEmpty();
    }
}
