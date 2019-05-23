<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AddressFixtures
 *
 * @package App\DataFixtures
 */
class CountryFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $countries = json_decode(file_get_contents(__DIR__ . '/../Data/Countries.json'));

        foreach ($countries as $country) {
            $type = new Country();
            foreach ($country as $property => $value) {
                $type->{'set' . ucfirst($property)}($value);
            }

            $manager->persist($type);
        }

        $manager->flush();
    }
}