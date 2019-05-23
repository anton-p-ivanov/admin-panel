<?php

namespace App\Form\Price;

use App\Entity\Price\Discount;
use App\Form\Custom\DateTimeType;
use App\Form\Custom\SelectorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DiscountType
 * @package App\Form\Price
 */
class DiscountType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * ManagerType constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $manager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', SelectorType::class, [
                'required' => false,
                'url' => $this->urlGenerator->generate('account_index', ['view' => 'selector']),
                'class' => \App\Entity\Account\Account::class
            ])
            ->add('value', TextType::class)
            ->add('expiredAt', DateTimeType::class, ['required' => false])
            ->add('isExpired', CheckboxType::class, ['required' => false]);

        $builder->get('value')->addModelTransformer(new CallbackTransformer(
            function (?string $value): ?string { return $value * 100; },
            function (string $value): string { return $value / 100; }
        ));

        $builder->get('isExpired')->addModelTransformer(new CallbackTransformer(
            function (bool $value): bool { return !$value; },
            function (bool $value): bool { return !$value; }
        ));

        $builder->get('account')->addModelTransformer(new CallbackTransformer(
            function ($value) { return $value; },
            function ($value) { return $this->manager->getRepository('App:Account\Account')->findOneBy(['uuid' => $value]); }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Discount::class,
            'translation_domain' => false
        ]);
    }
}
