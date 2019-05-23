<?php

namespace App\DataFixtures;

use App\Entity\Price\Currency;
use App\Entity\Price\Vat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Price
 *
 * @package App\DataFixtures
 */
class Price extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $currencies = json_decode(file_get_contents(__DIR__ . '/../Data/Currency.json'));
        $vats = json_decode(file_get_contents(__DIR__ . '/../Data/Vat.json'));

        foreach ($currencies as $currency) {
            $entity = new Currency();
            foreach ($currency as $property => $value) {
                $entity->{'set' . ucfirst($property)}($value);
            }

            $manager->persist($entity);
        }

        foreach ($vats as $vat) {
            $entity = new Vat();
            foreach ($vat as $property => $value) {
                $entity->{'set' . ucfirst($property)}($value);
            }

            $manager->persist($entity);
        }

        $manager->flush();
    }
}