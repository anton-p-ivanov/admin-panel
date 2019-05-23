<?php

namespace App\Form\Account;

use App\Entity\Account\Manager;
use App\Entity\User\User;
use App\Form\Custom\DateTimeType;
use App\Form\Custom\SelectorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ManagerType
 * @package App\Form
 */
class ManagerType extends AbstractType
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
     * @param EntityManagerInterface $manager
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
            ->add('manager', SelectorType::class, [
                'required' => true,
                'url' => $this->urlGenerator->generate('user_index', ['view' => 'selector']),
                'class' => User::class
            ])
            ->add('title', TextType::class, ['required' => false])
            ->add('expiredAt', DateTimeType::class, ['required' => false,]);

        $builder->get('manager')->addModelTransformer(new CallbackTransformer(
            function ($value) { return $value; },
            function ($value) { return $this->manager->getRepository('App:User\User')->findOneBy(['uuid' => $value]); }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Manager::class,
            'translation_domain' => false
        ]);
    }
}
