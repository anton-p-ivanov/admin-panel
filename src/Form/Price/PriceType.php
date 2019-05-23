<?php

namespace App\Form\Price;

use App\Entity\Price\Currency;
use App\Entity\Price\Price;
use App\Entity\Price\Vat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PriceType
 * @package App\Form\Price
 */
class PriceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', EntityType::class, [
                'required' => false,
                'placeholder' => false,
                'class' => Currency::class,
                'choice_label' => 'title',
                'expanded' => true
            ])
            ->add('vat', EntityType::class, [
                'required' => false,
                'placeholder' => false,
                'class' => Vat::class,
                'choice_label' => 'title',
                'expanded' => true
            ])
            ->add('minLevel', TextType::class, ['required' => false])
            ->add('comments', TextareaType::class, ['required' => false, 'attr' => ['rows' => 7]])
            ->add('brakes', CollectionType::class, [
                'required' => false,
                'entry_type' => BrakeType::class,
                'entry_options' => ['label' => false],
                'error_bubbling' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Price::class
        ]);
    }
}
