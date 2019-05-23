<?php

namespace App\Form\Field;

use App\Entity\Catalog\ElementValue;
use App\Entity\Field\Field;
use App\Form\Custom\SelectorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PropertyType
 * @package App\Form\Field
 */
class PropertyType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * PropertyType constructor.
     *
     * @param EntityManagerInterface $manager
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator
    ){
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var Field $field */
        $field = $options['fields'][(int)$builder->getName()];

        $baseAttr = [
            Field::FIELD_TYPE_TEXT => ['rows' => 10]
        ];

        $params = [
            'label' => $field->getLabel(),
            'help' => $field->getDescription(),
            'attr' => $field->getOptions() ? json_decode($field->getOptions(), true) : ($baseAttr[$field->getType()] ?? []),
        ];

        if ($field->isSelectable()) {
            $params['multiple'] = $field->isMultiple();
            $params['expanded'] = $field->isExpanded();
            $params['placeholder'] = '- Не выбрано -';
        }

        switch ($field->getType()) {
            case Field::FIELD_TYPE_TEXT:
                $type = TextareaType::class;
                $params['attr']['placeholder'] = 'Пусто';
                break;
            case Field::FIELD_TYPE_CHOICE:
                $type = ChoiceType::class;
                $params['choices'] = $field->getValuesArray();
                break;
            case Field::FIELD_TYPE_ENTITY:
                $catalogUuid = $field->getValues()->first()->getValue();
                /* @var \App\Entity\Catalog\Catalog $catalog */
                $catalog = $this->manager->getRepository('App:Catalog\Catalog')->find($catalogUuid);

                $type = SelectorType::class;
                $params['class'] = \App\Entity\Catalog\Element::class;
                $params['multiple'] = $field->isMultiple();
                $params['url'] = $this->urlGenerator->generate('catalog_element_index', [
                    'uuid' => $catalog->getTree()->getUuid(),
                    'view' => 'selector',
                    'multiple' => $field->isMultiple()
                ]);
                break;
            case Field::FIELD_TYPE_ACCOUNT:
                $type = ChoiceType::class;
                $params['choices'] = $this->getAccounts();
                break;
            case Field::FIELD_TYPE_USER:
                $type = ChoiceType::class;
                $params['choices'] = $this->getUsers();
                break;
            case Field::FIELD_TYPE_COUNTRY:
                $type = ChoiceType::class;
                $params['choices'] = $this->getCountries();
                break;
            case Field::FIELD_TYPE_FILE:
                $type = SelectorType::class;
                $params['class'] = \App\Entity\Storage\Storage::class;
                $params['url'] = $this->urlGenerator->generate('storage_index', [
                    'uuid' => 'root',
                    'view' => 'selector'
                ]);
                break;
            default:
                $type = TextType::class;
                $params['attr']['placeholder'] = 'Пусто';
                break;
        }

        $builder->add('value', $type, $params);
        if ($type !== SelectorType::class) {
            $builder->get('value')->addModelTransformer(new CallbackTransformer(
                function ($value) use ($field) { return $this->transform($value, $field); },
                function ($value) { return $this->reverseTransform($value); }
            ));
        }
    }

    /**
     * @param mixed $value
     * @param Field $field
     *
     * @return array|mixed
     */
    private function transform($value, Field $field)
    {
        if ($field->isMultiple()) {
            $value = $value ? json_decode($value, true) : [];
            if (!is_array($value)) {
                $value = [];
            }
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private function reverseTransform($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['fields']);
        $resolver->setDefaults([
            'data_class' => ElementValue::class,
            'translation_domain' => false
        ]);
    }

    /**
     * @return array
     */
    private function getAccounts(): array
    {
        $result = [];

        /* @var \App\Entity\Account\Account[] $accounts */
        $accounts = $this->manager->getRepository('App:Account\Account')->getAllAvailable();

        foreach ($accounts as $item) {
            $result[$item->getTitle()] = $item->getUuid();
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getUsers(): array
    {
        $result = [];

        /* @var \App\Entity\User\User[] $users */
        $users = $this->manager->getRepository('App:User\User')->getAllAvailable();

        foreach ($users as $item) {
            $result[$item->getFullName()] = $item->getUuid();
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getCountries(): array
    {
        $result = [];

        /* @var \App\Entity\Country[] $countries */
        $countries = $this->manager->getRepository('App:Country')->getAvailable();

        foreach ($countries as $item) {
            $result[$item->getTitle()] = $item->getCode();
        }

        return $result;
    }
}
