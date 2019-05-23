<?php

namespace App\Form;

use App\Entity\PartnershipDiscount;
use App\Entity\SalesDiscount;
use App\Form\Custom\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PartnershipDiscountType
 * @package App\Form
 */
class PartnershipDiscountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('discount', EntityType::class, [
                'class' => SalesDiscount::class,
                'choice_label' => 'title'
            ])
            ->add('value', TextType::class)
            ->add('expiredAt', DateTimeType::class, ['required' => false])
            ->add('isExpired', CheckboxType::class, ['required' => false]);

        $builder->get('value')->addModelTransformer(new CallbackTransformer(
            function (string $value): string { return $value * 100; },
            function (string $value): string { return $value / 100; }
        ));

        $builder->get('isExpired')->addModelTransformer(new CallbackTransformer(
            function (bool $value): bool { return !$value; },
            function (bool $value): bool { return !$value; }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PartnershipDiscount::class,
            'translation_domain' => false
        ]);
    }
}
