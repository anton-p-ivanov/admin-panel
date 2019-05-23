<?php

namespace App\DataFixtures;

use App\Entity\Storage\Storage;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class StorageFixtures
 *
 * @package App\DataFixtures
 */
class StorageFixtures extends Fixture implements DependentFixtureInterface
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
        for ($index = 0; $index < 30; $index++) {
            $storage = new Storage();
            $attributes = [
                'title' => $this->faker->text(50),
                'parentNodes' => new ArrayCollection(),
                'description' => $this->faker->text(),
                'workflow' => $this->setWorkflow($manager),
                'type' => $index > 4 ? Storage::STORAGE_TYPE_FILE : Storage::STORAGE_TYPE_DIR
            ];

            foreach ($attributes as $attribute => $value) {
                $storage->{'set' . ucfirst($attribute)}($value);
            }

            $manager->getRepository('App:Storage\Tree')->setTreeNode($storage);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            WorkflowStatusFixtures::class,
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