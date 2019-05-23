<?php

namespace App\Form;

use App\Entity\Mail\Template;
use App\Entity\Mail\Type;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MailTemplateType
 * @package App\Form
 */
class MailTemplateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class)
            ->add('code', TextType::class, ['required' => false])
            ->add('sender', EmailType::class, ['required' => false])
            ->add('recipient', EmailType::class)
            ->add('replyTo', EmailType::class, ['required' => false])
            ->add('copyTo', EmailType::class, ['required' => false])
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('textBody', TextareaType::class, ['required' => false])
            ->add('htmlBody', TextareaType::class, ['required' => false])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'title'
            ])
            ->add('sites', EntityType::class, [
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
            'data_class' => Template::class,
            'translation_domain' => 'mail',
            'attr' => ['novalidate' => true]
        ]);
    }
}
