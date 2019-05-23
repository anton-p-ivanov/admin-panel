<?php

namespace App\Form\Account;

use App\Entity\Account\Status;
use App\Entity\PartnershipStatus;
use App\Form\Custom\DateTimeType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StatusType
 * @package App\Form
 */
class StatusType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', EntityType::class, [
                'class' => PartnershipStatus::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->addOrderBy('t.sort', 'ASC')
                        ->addOrderBy('t.title', 'ASC');
                },
            ])
            ->add('isExpired', CheckboxType::class, ['required' => false])
            ->add('expiredAt', DateTimeType::class, ['required' => false]);

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
            'data_class' => Status::class,
            'translation_domain' => false
        ]);
    }
}
