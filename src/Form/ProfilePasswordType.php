<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ProfilePasswordType
 *
 * @package App\Form
 */
class ProfilePasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $option
     */
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('username', TextType::class, ['label' => 'form.password.username'])
            ->add('checkword', TextType::class, ['label' => 'form.password.checkword'])
            ->add('password', PasswordType::class, ['label' => 'form.password.password'])
            ->add('password_repeat', PasswordType::class, ['label' => 'form.password.password_repeat']);
    }
}