<?php

namespace App\Form\Catalog;

use App\Entity\Catalog\Element;
use App\Entity\Catalog\Tree;
use App\Entity\Role;
use App\Entity\Site;
use App\Entity\WorkflowStatus;
use App\Form\Custom\DateTimeType;
use App\Form\Field\PropertyType;
use App\Form\Price\PriceType;
use App\Repository\Catalog\CatalogTreeRepository;
use App\Repository\RoleRepository;
use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ElementType
 * @package App\Form\Catalog
 */
class ElementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var Element $element */
        $element = $builder->getData();

        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('activeFrom', DateTimeType::class, ['required' => false])
            ->add('activeTo', DateTimeType::class, ['required' => false])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('code', TextType::class, ['required' => false])
            ->add('sort', TextType::class, ['required' => false])
            ->add('parentNodes', EntityType::class, [
                'required' => false,
                'attr' => ['size' => 10],
                'class' => Tree::class,
                'query_builder' => function (CatalogTreeRepository $repository) use ($element) {
                    return $repository->getTreeQueryBuilder($element->getCatalog()->getTree(), $element);
                },
                'choice_label' => function (Tree $node) {
                    return str_repeat('-- ', $node->getLevel()) . $node->getElement()->getTitle();
                },
                'multiple' => !$element->isSection(),
                'placeholder' => false
            ])
            ->add('sites', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'title',
                'query_builder' => function (SiteRepository $repository) {
                    return $repository->getAvailableQueryBuilder();
                },
                'multiple' => true,
                'expanded' => true
            ])
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'title',
                'query_builder' => function (RoleRepository $repository) {
                    return $repository->getAvailableQueryBuilder();
                },
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

        if (!$element->isSection()) {
            $builder->add('content', TextareaType::class, ['required' => false]);

            if ($fields = $options['fields']) {
                $builder->add('values', CollectionType::class, [
                    'required' => false,
                    'entry_type' => PropertyType::class,
                    'entry_options' => ['label' => false, 'fields' => $fields],
                    'by_reference' => false,
                ]);
            }

            if ($element->getCatalog()->isTrading()) {
                $builder->add('price', PriceType::class);
            }
        }
        else {
            $builder->get('parentNodes')->addModelTransformer(
                new CallbackTransformer(
                    function (ArrayCollection $nodes) {
                        return $nodes->isEmpty() ? null : $nodes->first();
                    },
                    function ($value) {
                        return new ArrayCollection([$value]);
                    }
                )
            );
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['fields']);
        $resolver->setDefaults([
            'data_class' => Element::class
        ]);
    }
}
