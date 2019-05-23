<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures
 *
 * @package App\DataFixtures
 */
class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_REFERENCE = 'user';
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var Factory
     */
    private $faker;

    /**
     * UserPasswordFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;

        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $attributes = [
            'email' => 'guest.user@email.com',
            'fname' => 'Guest',
            'lname' => 'User',
            'sname' => '',
            'password' => 'P@ssw0rd',
            'isConfirmed' => true,
            'userRoles' => $manager->getRepository('App:Role')->findBy(['code' => 'ROLE_USER']),
            'workflow' => $this->setWorkflow($manager),
        ];

        foreach ($attributes as $name => $value) {
            $user->{'set' . ucfirst($name)}($value);
        }

        $manager->persist($user);

        $this->addFakeUsers($manager);

        $manager->flush();

        // other fixtures can get this object using the UserFixtures::USER_REFERENCE constant
        $this->addReference(self::USER_REFERENCE, $user);
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            WorkflowStatusFixtures::class,
            MailTemplateFixtures::class,
            RoleFixtures::class
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    private function addFakeUsers(ObjectManager $manager)
    {
        $roles = $manager->getRepository('App:Role')->findBy(['code' => 'ROLE_USER']);
        $sites = $manager->getRepository('App:Site')->findAll();

        for ($i = 0; $i < 25; $i++) {
            $user = new User();
            $attributes = [
                'email' => $this->faker->email,
                'fname' => $this->faker->firstName,
                'lname' => $this->faker->lastName,
                'sname' => '',
                'phone' => $this->faker->phoneNumber,
                'birthdate' => $this->faker->dateTime(),
                'isConfirmed' => true,
                'userRoles' => $roles,
                'userSites' => $sites,
                'workflow' => $this->setWorkflow($manager),
            ];

            foreach ($attributes as $name => $value) {
                $user->{'set'.ucfirst($name)}($value);
            }

            $manager->persist($user);
        }
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