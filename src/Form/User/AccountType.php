<?php

namespace App\Form\User;

use App\Entity\User\Account;
use App\Form\Custom\SelectorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AccountType
 * @package App\Form\User
 */
class AccountType extends AbstractType
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
                'required' => true,
                'url' => $this->urlGenerator->generate('account_index', ['view' => 'selector']),
                'class' => \App\Entity\Account\Account::class
            ])
            ->add('position', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Значение на задано']]);

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
            'data_class' => Account::class,
            'translation_domain' => false
        ]);
    }
}
