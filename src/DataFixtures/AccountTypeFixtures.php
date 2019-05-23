<?php

namespace App\DataFixtures;

use App\Entity\Account\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AccountTypeFixtures
 *
 * @package App\DataFixtures
 */
class AccountTypeFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = [
            'Партнёр',
            'Плательщик',
            'Грузополучатель',
            'Заказчик',
        ];

        foreach ($types as $index => $type) {
            $properties = [
                'title' => $type,
                'isDefault' => $index === 0,
            ];

            $type = new Type();
            foreach ($properties as $property => $value) {
                $type->{'set' . ucfirst($property)}($value);
            }

            $manager->persist($type);
        }

        $manager->flush();
    }
}