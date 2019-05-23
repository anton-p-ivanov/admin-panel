<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Country;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType
 * @package App\Form
 */
class AddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultOptions = [
            'required' => false, 
            'attr' => ['placeholder' => 'Значение не задано']
        ];
        
        $builder
            ->add('type', EntityType::class, [
                'class' => \App\Entity\AddressType::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.sort', 'ASC')
                        ->orderBy('t.title', 'ASC');
                },
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.title', 'ASC');
                },
            ])
            ->add('region', TextType::class, $defaultOptions)
            ->add('district', TextType::class, $defaultOptions)
            ->add('city', TextType::class, [
                'attr' => ['placeholder' => 'Значение не задано']
            ])
            ->add('zip', TextType::class, $defaultOptions)
            ->add('address', TextType::class, [
                'attr' => ['placeholder' => 'Значение не задано']
            ]);

        $builder->get('address')->addModelTransformer(new CallbackTransformer(
            function (?string $value): ?string { return $value; },
            function (?string $value): ?string {
                if ($value) {
                    $value = preg_replace(['/[\s]{2,}/', '/([^\s])\.([^\s])/'], [' ', '${1}. ${2}'], $value);
                }

                return $value;
            }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'translation_domain' => false,
        ]);
    }
}
