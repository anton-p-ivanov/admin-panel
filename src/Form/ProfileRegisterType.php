<?php

namespace App\Form;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ProfileRegisterType
 *
 * @package App\Form
 */
class ProfileRegisterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $option
     */
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'form.profile.username',
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [new NotBlank()],
                'first_options' => ['label' => 'form.profile.password'],
                'second_options' => ['label' => 'form.profile.password_repeat']
            ])
            ->add('fname', TextType::class, ['label' => 'form.profile.first_name'])
            ->add('lname', TextType::class, ['label' => 'form.profile.last_name'])
            ->add('sname', TextType::class, ['label' => 'form.profile.middle_name', 'required' => false]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}