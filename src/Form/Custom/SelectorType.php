<?php

namespace App\Form\Custom;

use App\DataTransformer\SelectorTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SelectorType
 * @package App\Form\Custom
 */
class SelectorType extends AbstractType
{
    /**
     * @var SelectorTransformer
     */
    private $transformer;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * StorageType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SelectorTransformer $transformer
     */
    public function __construct(EntityManagerInterface $entityManager, SelectorTransformer $transformer)
    {
        $this->manager = $entityManager;
        $this->transformer = $transformer;
    }

    /**
     * @return null|string
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['url'] = $options['url'];
        $view->vars['multiple'] = $options['multiple'];

        if ($value = $view->vars['value']) {
            // @todo Next block can be removed when no {"img":"..."} values found in App\Entity\Catalog\ElementValue entity
            if (preg_match('/^\{"img":"([\w\-]+)"\}$/', $value, $matches)) {
                $qb = $this->manager->createQueryBuilder();
                $file = $qb
                    ->select(['v', 'f'])
                    ->from('App:Storage\Version', 'v')
                    ->innerJoin('v.file', 'f')
                    ->where('f.uuid = :file')
                    ->setParameter('file', $matches[1])
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($file) {
                    $value = $file->getStorage()->getUuid();
                    $view->vars['value'] = $value;
                }
            }

            if ($options['multiple']) {
                $selected = [];
                $value = json_decode($value);
                if ($value) {
                    $view->vars['attr']['data-title'] = 'Выбрано элементов: ' . count($value);
                    $entities = $this->manager->getRepository($options['class'])->findBy(['uuid' => $value]);
                    foreach ($entities as $entity) {
                        $selected[] = $entity;
                    }
                }
                else {
                    $view->vars['value'] = null;
                }

                $view->vars['selected'] = $selected;
            }
            else {
                $entity = $this->manager->getRepository($options['class'])->findOneBy(['uuid' => $value]);
                if ($entity) {
                    $view->vars['attr']['data-title'] = $entity->getTitle();
                }
            }
        }
        else {
            $view->vars['value'] = null;
        }

        if ($options['required']) {
            $view->vars['label_attr']['class'] = 'required';
        }
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'selector';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['url', 'class']);
        $resolver->setDefaults([
            'error_bubbling' => false,
            'multiple' => false,
            'selected' => null,
            'attr' => ['class' => 'form-control']
        ]);
    }
}
