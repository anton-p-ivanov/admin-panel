<?php

namespace App\DataFixtures;

use App\Entity\Account\Account;
use App\Entity\Account\Contact;
use App\Entity\Account\Discount;
use App\Entity\Account\Manager;
use App\Entity\Account\Status;
use App\Entity\Address;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class AccountFixtures
 *
 * @package App\DataFixtures
 */
class AccountFixtures extends Fixture implements DependentFixtureInterface
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
        $types = $manager->getRepository('App:Account\Type')->findAll();

        for ($index = 0; $index < 10; $index++) {
            $account = new Account();
            $properties = [
                'title' => $this->faker->company,
                'description' => $this->faker->realText(),
                'email' => $this->faker->email,
                'web' => $this->faker->url,
                'phone' => $this->faker->phoneNumber,
                'workflow' => $this->setWorkflow($manager),
                'types' => [$types[random_int(0, count($types) - 1)]]
            ];

            foreach ($properties as $property => $value) {
                $account->{'set' . ucfirst($property)}($value);
            }

            $this->setAddresses($account, $manager);
            $this->setStatuses($account, $manager);
            $this->setManagers($account, $manager);
            $this->setDiscounts($account, $manager);
            $this->setContacts($account);

            $manager->persist($account);
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
            CountryFixtures::class,
            AddressTypeFixtures::class,
            AccountTypeFixtures::class,
            PartnershipStatusFixtures::class,
            UserFixtures::class,
            SaleDiscountFixtures::class
        ];
    }

    /**
     * @param Account $account
     * @param ObjectManager $manager
     */
    private function setDiscounts(Account $account, ObjectManager $manager)
    {
        $discounts = $manager->getRepository('App:SalesDiscount')->findAll();

        foreach ($discounts as $discount) {
            $properties = [
                'value' => random_int(0, 50) / 100,
                'discount' => $discount
            ];
            $discount = new Discount();
            foreach ($properties as $property => $value) {
                $discount->{'set' . ucfirst($property)}($value);
            }

            $account->addDiscount($discount);
        }
    }

    /**
     * @param Account $account
     * @param ObjectManager $manager
     */
    private function setAddresses(Account $account, ObjectManager $manager)
    {
        $types = $manager->getRepository('App:AddressType')->findAll();
        $countries = $manager->getRepository('App:Country')->findAll();

        for ($i = 0; $i < 3; $i++) {
            $properties = [
                'type' => $types[$i],
                'country' => $countries[random_int(0, count($countries) - 1)],
                'region' => '',
                'district' => '',
                'city' => $this->faker->city,
                'zip' => $this->faker->postcode,
                'address' => $this->faker->address,
            ];

            $address = new Address();
            foreach ($properties as $property => $value) {
                $address->{'set'.ucfirst($property)}($value);
            }

            $account->addAddress($address);
        }
    }

    /**
     * @param Account $account
     */
    private function setContacts(Account $account)
    {
        for ($i = 0; $i < 3; $i++) {
            $properties = [
                'email' => $this->faker->email,
                'name' => $this->faker->name,
                'position' => $this->faker->jobTitle
            ];
            $contact = new Contact();
            foreach ($properties as $property => $value) {
                $contact->{'set' . ucfirst($property)}($value);
            }

            $account->addContact($contact);
        }
    }

    /**
     * @param Account $account
     * @param ObjectManager $manager
     */
    private function setStatuses(Account $account, ObjectManager $manager)
    {
        $statuses = $manager->getRepository('App:PartnershipStatus')->findAll();

        for ($i = 0; $i < 3; $i++) {
            $properties = [
                'status' => $statuses[random_int(0, count($statuses) - 1)],
            ];
            $status = new Status();
            foreach ($properties as $property => $value) {
                $status->{'set' . ucfirst($property)}($value);
            }

            $account->addStatus($status);
        }
    }

    /**
     * @param Account $account
     * @param ObjectManager $manager
     */
    private function setManagers(Account $account, ObjectManager $manager)
    {
        $users = $manager->getRepository('App:User\User')->findAll();

        for ($i = 0; $i < 3; $i++) {
            $properties = [
                'manager' => $users[random_int(0, count($users) - 1)],
                'title' => $this->faker->jobTitle
            ];
            $manager = new Manager();
            foreach ($properties as $property => $value) {
                $manager->{'set' . ucfirst($property)}($value);
            }

            $account->addManager($manager);
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