<?php

namespace App\Form\Storage;

use App\Entity\Role;
use App\Entity\Storage\Storage;
use App\Entity\Storage\Tree;
use App\Repository\Storage\StorageTreeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class StorageType
 * @package App\Form\Storage
 */
class StorageType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * ManagerType constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('parentNodes', EntityType::class, [
                'required' => false,
                'attr' => ['size' => 10],
                'class' => Tree::class,
                'query_builder' => function (StorageTreeRepository $repository) use ($entity) {
                    return $repository->getTreeQueryBuilder($entity);
                },
                'choice_label' => function (Tree $node) use ($entity) {
                    return str_repeat('-- ', $node->getLevel() + (int) $entity->isDirectory()) . $node->getStorage()->getTitle();
                },
                'multiple' => !$entity->isDirectory(),
                'placeholder' => $entity->isDirectory() ? 'Библиотека файлов' : false
            ])
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
            ]);

        if ($entity->isDirectory()) {
            $builder->get('parentNodes')->addModelTransformer(new CallbackTransformer(
                function (ArrayCollection $nodes) { return $nodes->isEmpty() ? null : $nodes->first(); },
                function ($value) { return $value ? new ArrayCollection([$value]) : new ArrayCollection(); }
            ));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storage::class,
            'translation_domain' => false
        ]);
    }
}
