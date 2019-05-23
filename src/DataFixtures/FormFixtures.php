<?php

namespace App\DataFixtures;

use App\Entity\Field\Field;
use App\Entity\Form\Form;
use App\Entity\Form\Status;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class FormFixtures
 *
 * @package App\DataFixtures
 */
class FormFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * TrainingFixtures constructor.
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
        $first_form = null;
        for ($index = 0; $index < 10; $index++) {
            $form = new Form();
            $properties = [
                'title' => $this->faker->text,
                'description' => $this->faker->realText(),
                'workflow' => $this->setWorkflow($manager),
                'activeFrom' => new \DateTime(),
                'activeTo' => (new \DateTime())->modify('+1 day'),
            ];

            foreach ($properties as $property => $value) {
                $form->{'set' . ucfirst($property)}($value);
            }

            $this->setStatuses($form);

            if ($index === 0) {
                $first_form = $form;
            }

            $manager->persist($form);
        }

        $manager->flush();

        $this->setFields($first_form, $manager);
    }

    /**+
     * @param Form $form
     */
    private function setStatuses(Form $form)
    {
        for ($i = 0; $i < 3; $i++) {
            $status = new Status();
            $properties = [
                'isDefault' => $i === 0,
                'title' => $this->faker->text,
                'description' => $this->faker->realText(),
            ];

            foreach ($properties as $property => $value) {
                $status->{'set'.ucfirst($property)}($value);
            }

            $form->addStatus($status);
        }
    }

    /**
     * @param Form $form
     * @param ObjectManager $manager
     */
    private function setFields(Form $form, ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $field = new Field();
            $properties = [
                'label' => $this->faker->text(30),
                'description' => $this->faker->realText(),
                'type' => array_keys(Field::getTypes())[$i],
                'hash' => $form->getHash()
            ];

            foreach ($properties as $property => $value) {
                $field->{'set'.ucfirst($property)}($value);
            }
            $form->addField($field);
        }

        $manager->persist($form);
        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            WorkflowStatusFixtures::class,
            SiteFixtures::class
        ];
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
                ->findOneBy(['isDefault' => true])
        );

        return $workflow;
    }
}