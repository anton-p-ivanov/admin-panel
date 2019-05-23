<?php

namespace App\Form\Account;

use App\Entity\Account\Code;
use App\Form\Custom\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CodeType
 * @package App\Form
 */
class CodeType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('expiredAt', DateTimeType::class, ['required' => false]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Code::class,
            'translation_domain' => false
        ]);
    }
}
