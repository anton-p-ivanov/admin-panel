<?php

namespace App\Form\Account;

use App\Entity\Account\Account;
use App\Entity\Site;
use App\Entity\WorkflowStatus;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AccountType
 * @package App\Form
 */
class AccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('email', TextType::class)
            ->add('web', TextType::class)
            ->add('phone', TextType::class, ['required' => false])
            ->add('sort', TextType::class, ['required' => false])
            ->add('sites', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'title',
                'query_builder' => function (SiteRepository $repository) {
                    return $repository->getAvailableQueryBuilder();
                },
                'translation_domain' => false,
                'error_bubbling' => false,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('types', EntityType::class, [
                'class' => \App\Entity\Account\Type::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('t')
                        ->addOrderBy('t.sort', 'ASC')
                        ->addOrderBy('t.title', 'ASC');
                },
                'translation_domain' => false,
                'error_bubbling' => false,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('status', EntityType::class, [
                'required' => false,
                'placeholder' => false,
                'class' => WorkflowStatus::class,
                'choice_label' => 'title',
                'expanded' => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('t')
                        ->addOrderBy('t.sort', 'ASC')
                        ->addOrderBy('t.title', 'ASC');
                },
            ]);

        // Append default schema to URL if none specified
        $builder->get('web')->addModelTransformer(new CallbackTransformer(
            function (?string $url): ?string { return $url; },
            function (?string $url): ?string {
                if ($url && !parse_url($url, PHP_URL_SCHEME)) {
                    $url = 'https://' . $url;
                }
                return $url;
            }
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
