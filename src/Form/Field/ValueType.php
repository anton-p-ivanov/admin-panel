<?php

namespace App\Form\Field;

use App\Entity\Field\Value;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ValueType
 * @package App\Form\Field
 */
class ValueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort', TextType::class, ['required' => false])
            ->add('value', TextType::class, ['attr' => ['placeholder' => 'Значение не задано']])
            ->add('label', TextType::class, ['attr' => ['placeholder' => 'Значение не задано']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Value::class,
            'translation_domain' => false
        ]);
    }
}
