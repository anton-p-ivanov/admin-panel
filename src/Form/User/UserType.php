<?php

namespace App\Form\User;

use App\Entity\Role;
use App\Entity\Site;
use App\Entity\User\User;
use App\Repository\RoleRepository;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType
 * @package App\Form\User
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('isConfirmed', CheckboxType::class, ['required' => false])
            ->add('email', TextType::class, ['attr' => ['placeholder' => 'username@email.com']])
            ->add('fname', TextType::class, ['attr' => ['placeholder' => 'Иван']])
            ->add('lname', TextType::class, ['attr' => ['placeholder' => 'Иванов']])
            ->add('sname', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Иванович']])
            ->add('phone', TextType::class, ['required' => false, 'attr' => ['placeholder' => '+1 (123) 1234567']])
            ->add('phone_mobile', TextType::class, ['required' => false, 'attr' => ['placeholder' => '+1 (123) 1234567']])
            ->add('skype', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'UserSkypeName']])
            ->add('birthdate', BirthdayType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('password', RepeatedType::class, [
                'required' => false,
                'type' => PasswordType::class,
                'first_options' => ['label' => 'form.profile.password'],
                'second_options' => ['label' => 'form.profile.password_repeat']
            ])
            ->add('userRoles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (RoleRepository $repository) {
                    return $repository->getAvailableQueryBuilder();
                }
            ])
            ->add('userSites', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (SiteRepository $repository) {
                    return $repository->getAvailableQueryBuilder();
                }
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => false
        ]);
    }
}
