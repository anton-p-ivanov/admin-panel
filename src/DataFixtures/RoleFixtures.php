<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class RoleFixtures
 *
 * @package App\DataFixtures
 */
class RoleFixtures extends Fixture
{
    /**
     * @var array
     */
    private $roles = [];

    /**
     * RoleFixtures constructor.
     */
    public function __construct()
    {
        $this->roles = [
            [
                'code' => 'ROLE_USER',
                'title' => 'Зарегистрированный пользователь',
                'isDefault' => true
            ],
            [
                'code' => 'ROLE_MANAGER',
                'title' => 'Управляющий',
                'isDefault' => false
            ],
            [
                'code' => 'ROLE_ADMIN',
                'title' => 'Администратор',
                'isDefault' => false
            ]
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->roles as $item) {
            $attributes = [
                'code' => $item['code'],
                'title' => $item['title'],
                'isDefault' => $item['isDefault']
            ];

            $role = new Role();
            foreach ($attributes as $name => $value) {
                $role->{'set' . ucfirst($name)}($value);
            }

            $manager->persist($role);
        }

        $manager->flush();
    }
}