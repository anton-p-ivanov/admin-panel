<?php

namespace App\DataFixtures;

use App\Entity\Catalog\Catalog;
use App\Entity\Catalog\Element;
use App\Entity\Catalog\Type;
use App\Entity\Field\Field;
use App\Entity\Field\Value;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class CatalogsFixtures
 *
 * @package App\DataFixtures
 */
class CatalogsFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * CatalogsFixtures constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $first = null;
        for ($index = 0; $index < 5; $index++) {
            $catalogType = new Type();
            $attributes = [
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText()
            ];
            foreach ($attributes as $property => $value) {
                $catalogType->{'set' . ucfirst($property)}($value);
            }

            if ($index === 0) {
                $first = $catalogType;
            }

            $manager->persist($catalogType);
        }

        $first_catalog = null;
        for ($index = 0; $index < 5; $index++) {
            $catalog = new Catalog();
            $attributes = [
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText(),
                'type' => $first
            ];
            foreach ($attributes as $property => $value) {
                $catalog->{'set' . ucfirst($property)}($value);
            }

            if ($index === 0) {
                $first_catalog = $catalog;
            }

            $manager->persist($catalog);
        }

        $manager->flush();

        $this->setFields($first_catalog, $manager);
        $this->setElements($first_catalog, $manager);
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            WorkflowStatusFixtures::class,
        ];
    }

    /**
     * @param Catalog $catalog
     * @param ObjectManager $manager
     */
    private function setFields(Catalog $catalog, ObjectManager $manager)
    {
        for ($i = 0; $i < count(Field::getTypes()); $i++) {
            $field = new Field();
            $type = array_keys(Field::getTypes())[$i];
            $properties = [
                'label' => $this->faker->text(30),
                'description' => $this->faker->realText(),
                'type' => $type,
                'hash' => $catalog->getHash(),
                'workflow' => $this->setWorkflow($manager)
            ];

            foreach ($properties as $property => $value) {
                $field->{'set'.ucfirst($property)}($value);
            }

            if ($field->isSelectable()) {
                $this->setValues($field);
            }

            $catalog->addField($field);
        }

        $manager->persist($catalog);
        $manager->flush();
    }

    /**
     * @param Field $field
     */
    private function setValues(Field $field)
    {
        for ($i = 0; $i < 5; $i++) {
            $value = new Value();
            $properties = [
                'label' => $this->faker->text(30),
                'value' => $i,
                'sort' => ($i + 1) * 100,
            ];

            foreach ($properties as $property => $val) {
                $value->{'set'.ucfirst($property)}($val);
            }

            $field->addValue($value);
        }
    }

    /**
     * @param Catalog $catalog
     * @param ObjectManager $manager
     */
    private function setElements(Catalog $catalog, ObjectManager $manager)
    {
        $parentNodes = new ArrayCollection();
        $parentNodes->add($catalog->getTree());

        for ($i = 0; $i < 30; $i++) {
            $element = new Element();
            $properties = [
                'catalog' => $catalog,
                'type' => $i < 5 ? Element::TYPE_SECTION : Element::TYPE_ELEMENT,
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText(),
                'parentNodes' => $parentNodes,
                'workflow' => $this->setWorkflow($manager)
            ];

            foreach ($properties as $property => $value) {
                $element->{'set'.ucfirst($property)}($value);
            }

            $manager->getRepository('App:Catalog\Tree')->setTreeNode($element);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Workflow
     */
    private function setWorkflow(ObjectManager $manager)
    {
        $workflow = new Workflow();
        $workflow->setIsDeleted(false);
        $workflow->setStatus(
            $manager
                ->getRepository('App:WorkflowStatus')
                ->findOneBy(['code' => 'PUBLISHED'])
        );

        return $workflow;
    }
}