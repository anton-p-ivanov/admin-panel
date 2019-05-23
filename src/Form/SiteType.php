<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SiteType
 * @package App\Form
 */
class SiteType extends AbstractType
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
            ->add('url', TextType::class)
            ->add('email', TextType::class)
            ->add('sort', TextType::class, ['required' => false])
            ->add('code', TextType::class, ['required' => false]);

        // Append default schema to URL if none specified
        $builder->get('url')->addModelTransformer(new CallbackTransformer(
            function (?string $url): ?string { return $url; },
            function (string $url): string {
                if (!parse_url($url, PHP_URL_SCHEME)) {
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
            'data_class' => Site::class,
        ]);
    }
}
