<?php

namespace App\Form\Catalog;

use App\Entity\Catalog\Catalog;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Type
 * @package App\Form\Catalog
 */
class CatalogType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('isTrading', CheckboxType::class, ['required' => false])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('code', TextType::class, ['required' => false])
            ->add('sort', TextType::class, ['required' => false])
            ->add('sites', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'title',
                'query_builder' => function (SiteRepository $repository) {
                    return $repository->getAvailableQueryBuilder();
                },
                'translation_domain' => false,
                'multiple' => true,
                'expanded' => true
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Catalog::class
        ]);
    }
}
