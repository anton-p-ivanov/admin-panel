<?php

namespace App\Form;

use App\Entity\SalesDiscount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SalesDiscountType
 * @package App\Form
 */
class SalesDiscountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('sort', TextType::class, ['required' => false])
            ->add('code', TextType::class, ['required' => false])
            ->add('value', TextType::class);

        // Append default schema to URL if none specified
        $builder->get('value')->addModelTransformer(new CallbackTransformer(
            function (string $value): string { return $value * 100; },
            function (string $value): string { return $value / 100; }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesDiscount::class
        ]);
    }
}
