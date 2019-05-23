<?php

namespace App\Form\Form;

use App\Entity\Form\Form;
use App\Entity\Mail\Template;
use App\Entity\WorkflowStatus;
use App\Form\Custom\DateTimeType;
use App\Repository\Mail\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FormType
 * @package App\Form\Form
 */
class FormType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * FormType constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var Form $entity */
        $entity = $builder->getData();

        $builder
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('activeFrom', DateTimeType::class, ['required' => false])
            ->add('activeTo', DateTimeType::class, ['required' => false])
            ->add('title', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('template', TextareaType::class, ['required' => false])
            ->add('sort', TextType::class, ['required' => false])
            ->add('mailTemplate', EntityType::class, [
                'required' => false,
                'class' => Template::class,
                'choice_label' => 'subject',
                'query_builder' => function (TemplateRepository $repository) use ($entity) {
                    $builder = $repository->createQueryBuilder('t');

                    return $builder
                        ->where($builder->expr()->eq('t.type', ':type'))
                        ->setParameter(':type', $entity->getMailTemplate()->getType());
                },
            ])
            ->add('status', EntityType::class, [
                'required' => false,
                'placeholder' => null,
                'class' => WorkflowStatus::class,
                'choice_label' => 'title',
                'expanded' => true,
                'empty_data' => $this->manager->getRepository('App:WorkflowStatus')->getDefault()->getUuid(),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->addOrderBy('u.sort', 'ASC')
                        ->addOrderBy('u.title', 'ASC');
                },
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Form::class,
        ]);
    }
}
