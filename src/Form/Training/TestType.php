<?php

namespace App\Form\Training;

use App\Entity\Training\Question;
use App\Entity\Training\Test;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TestType
 * @package App\Form\Training
 */
class TestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $test Test */
        $test = $builder->getData();

        $questions = [];
        $lessons = $test->getCourse()->getLessons();
        foreach ($lessons as $lesson) {
            foreach ($lesson->getQuestions() as $question) {
                $questions[] = $question;
            }
        }

        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('isRandomQuestions', CheckboxType::class, ['required' => false])
            ->add('isRandomAnswers', CheckboxType::class, ['required' => false])
            ->add('limitAttempts', TextType::class, ['required' => false])
            ->add('limitTime', TextType::class, ['required' => false])
            ->add('limitPercent', TextType::class, ['required' => false])
            ->add('limitValue', TextType::class, ['required' => false])
            ->add('limitQuestions', TextType::class, ['required' => false])
            ->add('questions', EntityType::class, [
                'class' => Question::class,
                'choice_label' => 'title',
                'choices' => $questions,
                'translation_domain' => false,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('submit', SubmitType::class)
            ->add('apply', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Test::class,
        ]);
    }
}
