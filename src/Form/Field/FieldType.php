<?php

namespace App\Form\Field;

use App\Entity\Field\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FieldType
 * @package App\Form\Field
 */
class FieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var Field $field */
        $field = $builder->getData();

        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('isMultiple', CheckboxType::class, [
                'required' => false,
                'disabled' => !$field->isSelectable()
            ])
            ->add('isExpanded', ChoiceType::class, [
                'required' => false,
                'disabled' => !$field->isSelectable(),
                'placeholder' => false,
                'choices' => [
                    'Выпадающий список' => false,
                    'Список выбора' => true
                ]
            ])
            ->add('inList', ChoiceType::class, [
                'required' => false,
                'placeholder' => false,
                'choices' => [
                    'Да' => true,
                    'Нет' => false,
                ]
            ])
            ->add('label', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('options', TextareaType::class, ['required' => false])
            ->add('code', TextType::class, ['required' => false])
            ->add('value', TextType::class, ['required' => false])
            ->add('sort', TextType::class, ['required' => false])
            ->add('type', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip(Field::getTypes())
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Field::class,
            'translation_domain' => false
        ]);
    }
}
