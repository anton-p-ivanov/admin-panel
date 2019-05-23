<?php

namespace App\DataFixtures;

use App\Entity\AddressType;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AddressFixtures
 *
 * @package App\DataFixtures
 */
class AddressTypeFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $addresses = ['Юридический', 'Физический', 'Почтовый'];
        foreach ($addresses as $index => $address) {
            $properties = [
                'title' => $address,
                'isDefault' => $index === 0,
                'workflow' => $this->setWorkflow($manager)
            ];

            $type = new AddressType();
            foreach ($properties as $property => $value) {
                $type->{'set' . ucfirst($property)}($value);
            }

            $manager->persist($type);
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
            CountryFixtures::class
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